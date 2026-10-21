<?php

use App\Exceptions\BookingException;
use App\Models\Appointment;
use App\Models\HospitalStock;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Tests\Support\BookingWorld;

/*
 * Proves AppointmentService's locking really protects against concurrent requests,
 * using a second database connection the way a second web request would.
 *
 * 1. Stale-snapshot races (the dangerous case). Under MySQL's REPEATABLE READ, a
 *    plain SELECT reads an old snapshot. We open a transaction and take a snapshot,
 *    then the other connection commits a competing booking (taking the last dose or
 *    the last place). Only a locking read sees that change, so booking must refuse.
 *    Remove a lock and these tests fail.
 * 2. Blocking: while the other connection holds a lock on a row the booking needs,
 *    the booking waits (and, with a 1 second timeout, gives up) instead of going ahead.
 *
 * Runs with DatabaseTruncation (see tests/Pest.php): the second connection can only
 * see committed rows.
 */

beforeEach(function () {
    $this->travelTo('2026-03-02 08:00:00');
    $this->world = BookingWorld::create(childAgeDays: 49);

    $config = config('database.connections.mysql');
    $this->other = new PDO(
        "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']}",
        $config['username'],
        $config['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION],
    );

    DB::statement('SET SESSION innodb_lock_wait_timeout = 1');
});

afterEach(function () {
    if ($this->other?->inTransaction()) {
        $this->other->rollBack();
    }
    while (DB::transactionLevel() > 0) {
        DB::rollBack();
    }
});

/** Start a transaction and fix its REPEATABLE READ snapshot now. */
function takeSnapshot(): void
{
    DB::beginTransaction();
    DB::select('SELECT COUNT(*) FROM appointments');
    DB::select('SELECT COUNT(*) FROM hospital_stock');
}

test('a booking sees the last dose taken by another request after its snapshot', function () {
    $w = $this->world;
    $slot = $w->slot();
    $batch = $w->batch('OPV');
    $batch->update(['quantity' => 1]);

    takeSnapshot();
    // Meanwhile another request reserves the last dose and commits.
    $this->other->exec("UPDATE hospital_stock SET reserved = 1 WHERE id = {$batch->id}");

    expect(fn () => $w->book(['OPV-1'], $slot))->toThrow(BookingException::class, 'not in stock');
    DB::rollBack();

    expect(Appointment::count())->toBe(0);
});

test('a booking sees the last place taken by another request after its snapshot', function () {
    $w = $this->world;
    $slot = $w->slot(capacity: 1);
    $rival = $w->childAged(49);
    $rivalDose = $w->dose('OPV-1', $rival);
    $batch = $w->batch('OPV');

    takeSnapshot();
    // Meanwhile another request books the only place in the slot and commits.
    $this->other->exec('INSERT INTO appointments (child_id, child_dose_id, hospital_id, time_slot_id, hospital_stock_id, status, created_at, updated_at) '
        ."VALUES ({$rival->id}, {$rivalDose->id}, {$w->hospital->id}, {$slot->id}, {$batch->id}, 'pending', NOW(), NOW())");

    expect(fn () => $w->book(['OPV-1'], $slot))->toThrow(BookingException::class, 'fully booked');
    DB::rollBack();

    expect(Appointment::where('child_id', $w->child->id)->count())->toBe(0);
});

test('a booking sees a dose booked by another request after its snapshot', function () {
    $w = $this->world;
    $slot = $w->slot();
    $other = $w->slot(4);
    $dose = $w->dose('OPV-1');
    $batch = $w->batch('OPV');

    takeSnapshot();
    $this->other->exec('INSERT INTO appointments (child_id, child_dose_id, hospital_id, time_slot_id, hospital_stock_id, status, created_at, updated_at) '
        ."VALUES ({$w->child->id}, {$dose->id}, {$w->hospital->id}, {$other->id}, {$batch->id}, 'pending', NOW(), NOW())");

    expect(fn () => $w->book(['OPV-1'], $slot))->toThrow(BookingException::class, 'already has an appointment');
    DB::rollBack();
});

test('booking waits while another request holds the stock batch', function () {
    $w = $this->world;
    $slot = $w->slot();
    $batch = $w->batch('OPV');

    $this->other->beginTransaction();
    $this->other->query("SELECT * FROM hospital_stock WHERE id = {$batch->id} FOR UPDATE");

    expect(fn () => $w->book(['OPV-1'], $slot))->toThrow(QueryException::class, 'Lock wait timeout');
    expect(Appointment::count())->toBe(0)->and(HospitalStock::find($batch->id)->reserved)->toBe(0);
});

test('booking waits while another request holds the time slot', function () {
    $w = $this->world;
    $slot = $w->slot();

    $this->other->beginTransaction();
    $this->other->query("SELECT * FROM time_slots WHERE id = {$slot->id} FOR UPDATE");

    expect(fn () => $w->book(['OPV-1'], $slot))->toThrow(QueryException::class, 'Lock wait timeout');
    expect(Appointment::count())->toBe(0);
});

test('booking waits while another request holds the dose', function () {
    $w = $this->world;
    $slot = $w->slot();
    $dose = $w->dose('OPV-1');

    $this->other->beginTransaction();
    $this->other->query("SELECT * FROM child_doses WHERE id = {$dose->id} FOR UPDATE");

    expect(fn () => $w->book(['OPV-1'], $slot))->toThrow(QueryException::class, 'Lock wait timeout');
    expect(Appointment::count())->toBe(0);
});
