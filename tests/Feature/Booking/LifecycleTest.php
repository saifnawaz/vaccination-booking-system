<?php

use App\Enums\AppointmentStatus;
use App\Enums\DoseSource;
use App\Enums\DoseStatus;
use App\Exceptions\BookingException;
use App\Models\Appointment;
use App\Models\Hospital;
use App\Models\HospitalStock;
use App\Models\User;
use App\Models\Vaccine;
use Tests\Support\BookingWorld;

beforeEach(function () {
    $this->travelTo('2026-03-02 08:00:00'); // a Monday
    $this->world = BookingWorld::create(childAgeDays: 49);
});

function bookOne(BookingWorld $w, int $daysAhead = 3, string $label = 'Pentavalent-1'): Appointment
{
    return $w->book([$label], $w->slot($daysAhead))->sole();
}

// --- Hospital actions -----------------------------------------------------------

test('a hospital confirms a booking', function () {
    $w = $this->world;
    $a = bookOne($w);

    $this->actingAs($w->hospital->user)->patch(route('hospital.appointments.approve', $a))->assertSessionHas('success');

    expect($a->fresh()->status)->toBe(AppointmentStatus::Approved)->and($a->fresh()->approved_at)->not->toBeNull();
});

test('declining needs a reason and gives the dose back', function () {
    $w = $this->world;
    $a = bookOne($w);

    $this->actingAs($w->hospital->user)->patch(route('hospital.appointments.reject', $a), ['reason' => ''])
        ->assertSessionHasErrors('reason', null, 'reject'.$a->id);
    expect($a->fresh()->status)->toBe(AppointmentStatus::Pending);

    $this->patch(route('hospital.appointments.reject', $a), ['reason' => 'Closed for a holiday'])->assertSessionHas('success');

    expect($a->fresh()->status)->toBe(AppointmentStatus::Rejected)
        ->and($a->fresh()->reason)->toBe('Closed for a holiday')
        ->and($w->batch('PENTA')->fresh()->reserved)->toBe(0);
});

test('completing uses up the dose and records it on the schedule', function () {
    $w = $this->world;
    $a = bookOne($w, daysAhead: 1);
    $w->service->approve($a);
    $batch = $w->batch('PENTA');

    $this->travelTo('2026-03-03 10:00:00');
    $this->actingAs($w->hospital->user)->patch(route('hospital.appointments.complete', $a))->assertSessionHas('success');

    $dose = $w->dose('Pentavalent-1');
    expect($a->fresh()->status)->toBe(AppointmentStatus::Completed)
        ->and($batch->fresh()->quantity)->toBe(19)
        ->and($batch->fresh()->reserved)->toBe(0)
        ->and($dose->administered_at->toDateString())->toBe('2026-03-03')
        ->and($dose->source)->toBe(DoseSource::GivenHere)
        ->and($dose->hospital_stock_id)->toBe($batch->id)
        ->and($dose->batch_number)->toBe('PENTA-A')
        ->and($dose->administered_by_hospital_id)->toBe($w->hospital->id);

    $scheduled = $w->child->fresh()->schedule()->doses()->first(fn ($d) => $d->label() === 'Pentavalent-1');
    expect($scheduled->status)->toBe(DoseStatus::Completed)
        ->and($scheduled->summary())->toContain('at '.$w->hospital->name);
});

test('an appointment cannot be completed before its date', function () {
    $w = $this->world;
    $a = bookOne($w);
    $w->service->approve($a);

    expect(fn () => $w->service->complete($a))->toThrow(BookingException::class, 'on or after its date');
    expect($w->batch('PENTA')->fresh()->quantity)->toBe(20);
});

test('a no-show gives the dose back, only after the slot has started', function () {
    $w = $this->world;
    $a = bookOne($w, daysAhead: 1);
    $w->service->approve($a);

    expect(fn () => $w->service->markNoShow($a))->toThrow(BookingException::class, 'once its time has started');

    $this->travelTo('2026-03-03 09:05:00');
    $w->service->markNoShow($a);

    expect($a->fresh()->status)->toBe(AppointmentStatus::NoShow)
        ->and($a->fresh()->no_show_at)->not->toBeNull()
        ->and($w->batch('PENTA')->fresh()->reserved)->toBe(0)
        ->and($w->batch('PENTA')->fresh()->quantity)->toBe(20);
});

test('only valid status changes are allowed', function (Closure $setup, string $action) {
    $w = $this->world;
    $a = bookOne($w, daysAhead: 1);
    $setup($w, $a);
    $this->travelTo('2026-03-03 10:00:00');
    $before = $a->fresh()->status;

    expect(fn () => match ($action) {
        'approve' => $w->service->approve($a),
        'complete' => $w->service->complete($a),
        'no_show' => $w->service->markNoShow($a),
        'reject' => $w->service->reject($a, 'x'),
    })->toThrow(BookingException::class);

    expect($a->fresh()->status)->toBe($before)->and(BookingWorld::reservationsAreConsistent())->toBeTrue();
})->with([
    'complete a pending one' => [fn () => null, 'complete'],
    'no-show a pending one' => [fn () => null, 'no_show'],
    'approve a cancelled one' => [fn ($w, $a) => $w->service->cancel($a), 'approve'],
    'reject a cancelled one' => [fn ($w, $a) => $w->service->cancel($a), 'reject'],
    'approve a declined one' => [fn ($w, $a) => $w->service->reject($a, 'x'), 'approve'],
]);

test('a completed appointment can never be changed', function () {
    $w = $this->world;
    $a = bookOne($w, daysAhead: 1);
    $w->service->approve($a);
    $this->travelTo('2026-03-03 10:00:00');
    $w->service->complete($a);

    foreach ([fn () => $w->service->reject($a, 'x'), fn () => $w->service->markNoShow($a), fn () => $w->service->complete($a)] as $action) {
        expect($action)->toThrow(BookingException::class);
    }
    expect($a->fresh()->status)->toBe(AppointmentStatus::Completed);
});

test("a hospital cannot act on another hospital's appointment", function (string $route) {
    $w = $this->world;
    $a = bookOne($w);
    $other = Hospital::factory()->active()->create();

    $this->actingAs($other->user)->patch(route($route, $a), ['reason' => 'x'])->assertForbidden();
    $this->actingAs($other->user)->get(route('hospital.appointments.show', $a))->assertForbidden();

    expect($a->fresh()->status)->toBe(AppointmentStatus::Pending);
})->with(['hospital.appointments.approve', 'hospital.appointments.reject', 'hospital.appointments.complete', 'hospital.appointments.no-show']);

test("another hospital's appointments don't appear in my list", function () {
    $w = $this->world;
    bookOne($w);
    $other = Hospital::factory()->active()->create();

    $this->actingAs($other->user)->get(route('hospital.appointments.index'))
        ->assertOk()->assertDontSee($w->child->fullName());
    $this->actingAs($w->hospital->user)->get(route('hospital.appointments.index'))
        ->assertOk()->assertSee($w->child->fullName());
});

// --- Parent actions -------------------------------------------------------------

test('a parent cancels and the dose goes back into stock', function () {
    $w = $this->world;
    $a = bookOne($w);

    $this->actingAs($w->parent)->patch(route('parent.appointments.cancel', $a))->assertRedirect(route('parent.appointments.index'));

    expect($a->fresh()->status)->toBe(AppointmentStatus::Cancelled)
        ->and($a->fresh()->cancelled_at)->not->toBeNull()
        ->and($w->batch('PENTA')->fresh()->reserved)->toBe(0);
});

test('cancelling is allowed until exactly 24 hours before', function () {
    $w = $this->world;
    $a = bookOne($w, daysAhead: 2); // Wed 09:00

    $this->travelTo('2026-03-03 09:00:01'); // 23h59m before
    expect(fn () => $w->service->cancel($a))->toThrow(BookingException::class, '24 hours');

    $this->travelTo('2026-03-03 09:00:00'); // exactly 24h before
    expect($w->service->cancel($a)->status)->toBe(AppointmentStatus::Cancelled);
});

test("a parent cannot cancel or reschedule another parent's appointment", function () {
    $w = $this->world;
    $a = bookOne($w);
    $stranger = User::factory()->parent()->create();

    $this->actingAs($stranger)->patch(route('parent.appointments.cancel', $a))->assertForbidden();
    $this->actingAs($stranger)->patch(route('parent.appointments.reschedule.update', $a), ['slot' => $w->slot(5)->id])->assertForbidden();
    $this->actingAs($stranger)->get(route('parent.appointments.reschedule', $a))->assertForbidden();

    expect($a->fresh()->status)->toBe(AppointmentStatus::Pending);
});

test('rescheduling moves the visit and asks the hospital to confirm again', function () {
    $w = $this->world;
    $a = bookOne($w);
    $w->service->approve($a);
    $new = $w->slot(6, '11:00');

    $this->actingAs($w->parent)->patch(route('parent.appointments.reschedule.update', $a), ['slot' => $new->id])
        ->assertRedirect(route('parent.appointments.index'));

    $a->refresh();
    expect($a->time_slot_id)->toBe($new->id)
        ->and($a->status)->toBe(AppointmentStatus::Pending)
        ->and($a->approved_at)->toBeNull()
        ->and($w->batch('PENTA')->fresh()->reserved)->toBe(1);
});

test('rescheduling is refused within 24 hours, into a full slot, or at another hospital', function () {
    $w = $this->world;
    $a = bookOne($w, daysAhead: 3);
    $full = $w->slot(5, '09:00', capacity: 1);
    $w->book(['OPV-1'], $full, $w->childAged(49));
    $elsewhere = Hospital::factory()->active()->create();
    $w->stockEverything($elsewhere);

    expect(fn () => $w->service->reschedule($a, $full))->toThrow(BookingException::class, 'fully booked')
        ->and(fn () => $w->service->reschedule($a, $w->slot(5, hospital: $elsewhere)))->toThrow(BookingException::class, 'same hospital');

    $this->travelTo('2026-03-04 12:00:00'); // under 24h before Thu 09:00
    expect(fn () => $w->service->reschedule($a, $w->slot(6)))->toThrow(BookingException::class, '24 hours');

    expect($a->fresh()->time_slot_id)->not->toBe($full->id);
});

test('rescheduling past the reserved batch expiry swaps to another usable batch', function () {
    $w = $this->world;
    $penta = Vaccine::where('code', 'PENTA')->sole();
    $short = HospitalStock::factory()->create(['hospital_id' => $w->hospital->id, 'vaccine_id' => $penta->id, 'batch_number' => 'SHORT', 'expiry_date' => '2026-03-08']);
    $a = bookOne($w, daysAhead: 3);
    expect($a->hospital_stock_id)->toBe($short->id); // soonest-expiring first

    $w->service->reschedule($a, $w->slot(10)); // 12 March, after SHORT expires

    expect($a->fresh()->hospitalStock->batch_number)->toBe('PENTA-A')
        ->and($short->fresh()->reserved)->toBe(0)
        ->and($w->batch('PENTA')->fresh()->reserved)->toBe(1)
        ->and(BookingWorld::reservationsAreConsistent())->toBeTrue();
});

test('rescheduling past expiry is refused when no other batch lasts', function () {
    $w = $this->world;
    $w->batch('PENTA')->update(['expiry_date' => '2026-03-08']);
    $a = bookOne($w, daysAhead: 3);
    $slotBefore = $a->time_slot_id;

    expect(fn () => $w->service->reschedule($a, $w->slot(10)))->toThrow(BookingException::class, 'not in stock');

    expect($a->fresh()->time_slot_id)->toBe($slotBefore)
        ->and($w->batch('PENTA')->fresh()->reserved)->toBe(1);
});

// --- Earlier doses from previous records ----------------------------------------

test('a hospital records an earlier dose from the child card', function () {
    $w = BookingWorld::create(childAgeDays: 120);
    $a = $w->book(['BCG'], $w->slot())->sole();

    $this->actingAs($w->hospital->user)->post(route('hospital.appointments.history', $a), [
        'child_dose_id' => $w->dose('OPV-0')->id, 'given_on' => now()->subDays(119)->toDateString(), 'batch_number' => 'OLD-123',
    ])->assertSessionHas('success');

    $dose = $w->dose('OPV-0');
    expect($dose->source)->toBe(DoseSource::HistoricalRecord)
        ->and($dose->batch_number)->toBe('OLD-123')
        ->and($dose->hospital_stock_id)->toBeNull()
        ->and($dose->administered_by_hospital_id)->toBe($w->hospital->id);

    $summary = $w->child->fresh()->schedule()->doses()->first(fn ($d) => $d->label() === 'OPV-0')->summary();
    expect($summary)->toContain('recorded from previous records');
});

test('earlier doses are recorded in order and with sensible dates', function (Closure $payload, string $message) {
    $w = BookingWorld::create(childAgeDays: 120);
    $a = $w->book(['BCG'], $w->slot())->sole();

    $this->actingAs($w->hospital->user)->post(route('hospital.appointments.history', $a), $payload($w))
        ->assertSessionHas('error', fn ($error) => str_contains($error, $message));
})->with([
    'out of order' => [fn ($w) => ['child_dose_id' => $w->dose('OPV-2')->id, 'given_on' => now()->subDays(40)->toDateString()], 'Record OPV-1 first'],
    'before birth' => [fn ($w) => ['child_dose_id' => $w->dose('OPV-1')->id, 'given_on' => now()->subDays(200)->toDateString()], 'between the date of birth and today'],
    'past the age limit' => [fn ($w) => ['child_dose_id' => $w->dose('OPV-0')->id, 'given_on' => now()->subDays(60)->toDateString()], "can't be given after"],
    'has an open appointment' => [fn ($w) => ['child_dose_id' => $w->dose('BCG')->id, 'given_on' => now()->subDays(100)->toDateString()], 'open appointment'],
]);

test('a hospital without an appointment for the child cannot record doses', function () {
    $w = BookingWorld::create(childAgeDays: 120);
    $a = $w->book(['BCG'], $w->slot())->sole();
    $other = Hospital::factory()->active()->create();

    $this->actingAs($other->user)->post(route('hospital.appointments.history', $a), [
        'child_dose_id' => $w->dose('OPV-0')->id, 'given_on' => now()->subDays(119)->toDateString(),
    ])->assertForbidden();

    expect($w->dose('OPV-0')->administered_at)->toBeNull();
});

// --- Slots and hospitals with bookings ------------------------------------------

test('a booked slot cannot be removed or shrunk below its bookings', function () {
    $w = $this->world;
    $slot = $w->slot(3, capacity: 4);
    $w->book(['OPV-1'], $slot);
    $w->book(['OPV-1'], $slot, $w->childAged(49));

    $this->actingAs($w->hospital->user);
    $this->delete(route('hospital.slots.destroy', $slot))->assertSessionHas('error');
    $this->put(route('hospital.slots.update', $slot), ['capacity' => 1])->assertSessionHasErrors('capacity');
    $this->put(route('hospital.slots.update', $slot), ['capacity' => 2])->assertSessionHasNoErrors();

    expect($slot->fresh())->not->toBeNull()->and($slot->fresh()->capacity)->toBe(2);
});

test('a hospital with open appointments cannot be deleted', function () {
    $w = $this->world;
    $a = bookOne($w);
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->delete(route('admin.hospitals.destroy', $w->hospital))->assertSessionHas('error');
    $this->assertNotSoftDeleted($w->hospital);

    $w->service->cancel($a);
    $this->actingAs($admin)->delete(route('admin.hospitals.destroy', $w->hospital))->assertRedirect(route('admin.hospitals.index'));
    $this->assertSoftDeleted($w->hospital);
});

// --- Pages ----------------------------------------------------------------------

test('appointment pages render for parent and hospital', function () {
    $w = $this->world;
    $a = bookOne($w);

    $this->actingAs($w->parent)->get(route('parent.appointments.index'))
        ->assertOk()->assertSee('Pentavalent-1')->assertSee('Awaiting confirmation')->assertSee('Change time');
    $this->actingAs($w->parent)->get(route('parent.appointments.reschedule', $a))->assertOk()->assertSee('Choose a new time');
    $this->actingAs($w->parent)->get(route('parent.dashboard'))->assertOk()->assertSee('Next appointments');

    $this->actingAs($w->hospital->user)->get(route('hospital.appointments.show', $a))
        ->assertOk()->assertSee($w->child->fullName())->assertSee('Record an earlier dose');
    $this->actingAs($w->hospital->user)->get(route('hospital.dashboard'))->assertOk()->assertSee('Bookings to confirm');
});

test('stock stays consistent through a whole sequence of changes', function () {
    $w = $this->world;
    $others = collect(range(1, 3))->map(fn () => $w->childAged(49));

    $a = $w->book(['OPV-1', 'Pentavalent-1'], $w->slot(3, '09:00'));
    $b = $w->book(['OPV-1'], $w->slot(3, '10:00'), $others[0])->sole();
    $c = $w->book(['OPV-1'], $w->slot(4, '09:00'), $others[1])->sole();
    $d = $w->book(['OPV-1'], $w->slot(1, '11:00'), $others[2])->sole();

    $w->service->cancel($b);
    $w->service->reject($c, 'x');
    $w->service->reschedule($a[0], $w->slot(5, '09:00'));
    $w->service->approve($d);
    $this->travelTo('2026-03-03 12:00:00');
    $w->service->complete($d);

    expect(BookingWorld::reservationsAreConsistent())->toBeTrue()
        ->and($w->batch('OPV')->fresh()->quantity)->toBe(19)
        ->and($w->batch('OPV')->fresh()->reserved)->toBe(1);
});
