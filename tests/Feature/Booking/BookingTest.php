<?php

use App\Enums\AppointmentStatus;
use App\Enums\DoseStatus;
use App\Enums\HospitalStatus;
use App\Exceptions\BookingException;
use App\Models\Appointment;
use App\Models\Hospital;
use App\Models\HospitalStock;
use App\Models\User;
use App\Models\Vaccine;
use App\Services\ScheduledDose;
use Illuminate\Database\UniqueConstraintViolationException;
use Tests\Support\BookingWorld;

beforeEach(function () {
    $this->travelTo('2026-03-02 08:00:00'); // a Monday
    $this->world = BookingWorld::create(childAgeDays: 49); // 7 weeks: 6-week doses due
});

$sixWeek = ['OPV-1', 'Pentavalent-1', 'PCV-1', 'Rota-1'];

test('a parent books a visit through the confirm form', function () use ($sixWeek) {
    $w = $this->world;
    $slot = $w->slot();

    $this->actingAs($w->parent)->post(route('parent.appointments.store'), [
        'child' => $w->child->id,
        'slot' => $slot->id,
        'doses' => array_map(fn ($l) => $w->dose($l)->id, $sixWeek),
    ])->assertRedirect(route('parent.appointments.index'))->assertSessionHas('success');

    expect(Appointment::where('status', AppointmentStatus::Pending)->count())->toBe(4)
        ->and($w->batch('PENTA')->fresh()->reserved)->toBe(1)
        ->and(BookingWorld::reservationsAreConsistent())->toBeTrue();
});

test('one child with several vaccines takes one place in the slot', function () use ($sixWeek) {
    $w = $this->world;
    $slot = $w->slot(capacity: 1);

    $w->book($sixWeek, $slot);

    expect($slot->bookedChildren())->toBe(1);
});

test('a slot cannot be booked past its capacity', function () {
    $w = $this->world;
    $slot = $w->slot(capacity: 1);
    $other = $w->childAged(49);

    $w->book(['OPV-1'], $slot);

    expect(fn () => $w->book(['OPV-1'], $slot, $other))->toThrow(BookingException::class, 'fully booked');
    expect($slot->bookedChildren())->toBe(1);
});

test('a child already in a full slot can add another vaccine to the same visit', function () {
    $w = $this->world;
    $slot = $w->slot(capacity: 1);

    $w->book(['OPV-1'], $slot);
    $w->book(['PCV-1'], $slot);

    expect($slot->appointments()->open()->count())->toBe(2)->and($slot->bookedChildren())->toBe(1);
});

test('two bookings cannot take the last dose', function () {
    $w = $this->world;
    $w->batch('ROTA')->update(['quantity' => 1]);
    $other = $w->childAged(49);

    $w->book(['Rota-1'], $w->slot(3, '09:00'));

    expect(fn () => $w->book(['Rota-1'], $w->slot(3, '10:00'), $other))->toThrow(BookingException::class, 'not in stock');
    expect($w->batch('ROTA')->fresh()->reserved)->toBe(1);
});

test('the soonest-expiring usable batch is used first, never an expired one', function () {
    $w = $this->world;
    $penta = Vaccine::where('code', 'PENTA')->sole();
    HospitalStock::factory()->expired()->create(['hospital_id' => $w->hospital->id, 'vaccine_id' => $penta->id, 'batch_number' => 'EXPIRED', 'quantity' => 50]);
    $soon = HospitalStock::factory()->create(['hospital_id' => $w->hospital->id, 'vaccine_id' => $penta->id, 'batch_number' => 'SOON', 'expiry_date' => now()->addDays(10)->toDateString()]);

    $appointment = $w->book(['Pentavalent-1'], $w->slot(3))->sole();

    expect($appointment->hospital_stock_id)->toBe($soon->id);
});

test('a batch expiring before the appointment date is skipped', function () {
    $w = $this->world;
    $penta = Vaccine::where('code', 'PENTA')->sole();
    HospitalStock::factory()->create(['hospital_id' => $w->hospital->id, 'vaccine_id' => $penta->id, 'batch_number' => 'TOO-SOON', 'expiry_date' => now()->addDays(5)->toDateString()]);

    $appointment = $w->book(['Pentavalent-1'], $w->slot(10))->sole();

    expect($appointment->hospitalStock->batch_number)->toBe('PENTA-A');
});

test('a booking is all or nothing', function () {
    $w = $this->world;
    $w->batch('ROTA')->update(['quantity' => 0]);

    expect(fn () => $w->book(['OPV-1', 'Rota-1'], $w->slot()))->toThrow(BookingException::class);

    expect(Appointment::count())->toBe(0)
        ->and($w->batch('OPV')->fresh()->reserved)->toBe(0);
});

test('a dose cannot have two open bookings', function () {
    $w = $this->world;
    $w->book(['OPV-1'], $w->slot(3));

    expect(fn () => $w->book(['OPV-1'], $w->slot(4)))->toThrow(BookingException::class, 'already has an appointment');
});

test('the database itself rejects a second open booking for a dose', function () {
    $w = $this->world;
    $first = $w->book(['OPV-1'], $w->slot(3))->sole();

    $copy = $first->replicate(['open_child_dose_id']);
    $copy->time_slot_id = $w->slot(4)->id;

    expect(fn () => $copy->save())->toThrow(UniqueConstraintViolationException::class);
});

test('a dose can be booked again once its appointment is cancelled', function () {
    $w = $this->world;
    $first = $w->book(['OPV-1'], $w->slot(3))->sole();
    $w->service->cancel($first);

    $w->book(['OPV-1'], $w->slot(4));

    expect(Appointment::where('status', AppointmentStatus::Pending)->count())->toBe(1);
});

test('a dose cannot be booked before it is due', function () {
    // 5 weeks old: 6-week doses are due in 7 days.
    $w = BookingWorld::create(childAgeDays: 35);

    expect(fn () => $w->book(['OPV-1'], $w->slot(3)))->toThrow(BookingException::class, 'can only be given on or after');

    expect($w->book(['OPV-1'], $w->slot(8)))->toHaveCount(1); // an upcoming dose for a date when it's due is fine
});

test('a dose waiting for the previous one cannot be booked', function () {
    expect(fn () => $this->world->book(['OPV-2'], $this->world->slot(30)))->toThrow(BookingException::class, "can't be booked");
});

test('completed and missed doses cannot be booked', function () {
    $w = BookingWorld::create(childAgeDays: 140); // Rota-1 is past its age limit
    $bcg = $w->dose('BCG');
    $bcg->administered_at = now()->subDays(139);
    $bcg->save();

    expect(fn () => $w->book(['BCG'], $w->slot(3, '09:00')))->toThrow(BookingException::class, "can't be booked")
        ->and(fn () => $w->book(['Rota-1'], $w->slot(3, '10:00')))->toThrow(BookingException::class, "can't be booked");
});

test('a dose cannot be booked after its maximum age', function () {
    // 98 days old: Rota-1 can be given until day 105.
    $w = BookingWorld::create(childAgeDays: 98);

    expect($w->book(['Rota-1'], $w->slot(7)))->toHaveCount(1);
    expect(fn () => $w->book(['Rota-2'], $w->slot(8)))->toThrow(BookingException::class); // waits for Rota-1
});

test('late doses without an age limit stay bookable as catch-up', function () {
    // Two years old and nothing given at all.
    $w = BookingWorld::create(childAgeDays: 730);
    $schedule = $w->child->schedule();
    $byLabel = $schedule->doses()->keyBy(fn (ScheduledDose $d) => $d->label());

    // Only vaccines with a max_age_days are missed: the birth doses and rotavirus.
    expect($schedule->doses()->filter(fn ($d) => $d->status === DoseStatus::Missed)->map->label()->sort()->values()->all())
        ->toBe(['BCG', 'Hepatitis B birth dose', 'OPV-0', 'Rota-1', 'Rota-2']);

    foreach (['OPV-1', 'Pentavalent-1', 'PCV-1', 'IPV-1', 'MR-1', 'TCV'] as $label) {
        expect($byLabel[$label]->status)->toBe(DoseStatus::Overdue)
            ->and($byLabel[$label]->isBookable())->toBeTrue();
    }
    foreach (['OPV-2', 'Pentavalent-2', 'Pentavalent-3', 'PCV-3', 'MR-2'] as $label) {
        expect($byLabel[$label]->status)->toBe(DoseStatus::Upcoming); // waiting, not missed
    }

    expect($w->book(['Pentavalent-1', 'OPV-1', 'PCV-1'], $w->slot()))->toHaveCount(3);
});

test('inactive hospitals cannot take bookings and are not offered', function () {
    $w = $this->world;
    $slot = $w->slot();
    $w->hospital->transitionTo(HospitalStatus::Inactive, User::factory()->admin()->create());

    expect(fn () => $w->book(['OPV-1'], $slot))->toThrow(BookingException::class, 'not taking bookings');

    $this->actingAs($w->parent)
        ->get(route('parent.book.hospitals', [$w->child, 'doses' => [$w->dose('OPV-1')->id]]))
        ->assertOk()
        ->assertDontSee($w->hospital->name);
});

test('inactive vaccines cannot be booked', function () {
    $w = $this->world;
    Vaccine::where('code', 'OPV')->update(['is_active' => false]);

    expect(fn () => $w->book(['OPV-1'], $w->slot()))->toThrow(BookingException::class, 'not available');
});

test('a slot that has started cannot be booked', function () {
    $w = $this->world;
    $slot = $w->slot(0, '07:30'); // today, already started at 08:00

    expect(fn () => $w->book(['OPV-1'], $slot))->toThrow(BookingException::class, 'already passed');
});

test("a parent cannot book for another parent's child", function () {
    $w = $this->world;
    $stranger = User::factory()->parent()->create();

    $this->actingAs($stranger)->post(route('parent.appointments.store'), [
        'child' => $w->child->id, 'slot' => $w->slot()->id, 'doses' => [$w->dose('OPV-1')->id],
    ])->assertForbidden();

    $this->actingAs($stranger)->get(route('parent.book.doses', $w->child))->assertForbidden();

    expect(Appointment::count())->toBe(0);
});

test('the booking steps show only hospitals and times that work', function () use ($sixWeek) {
    $w = $this->world;
    $slot = $w->slot(3);
    $noStock = Hospital::factory()->active()->create(['name' => 'Empty Clinic']);
    $w->slot(3, hospital: $noStock);
    $doses = ['doses' => array_map(fn ($l) => $w->dose($l)->id, $sixWeek)];

    $this->actingAs($w->parent);

    $this->get(route('parent.book.doses', $w->child))->assertOk()->assertSee('OPV-1')->assertSee('Find a hospital');

    $this->get(route('parent.book.hospitals', [$w->child, ...$doses]))
        ->assertOk()->assertSee($w->hospital->name)->assertDontSee('Empty Clinic');

    $this->get(route('parent.book.slots', [$w->child, $w->hospital, ...$doses]))
        ->assertOk()->assertSee('9:00 am')->assertSee('4 places left');

    $this->get(route('parent.book.confirm', [$w->child, ...$doses, 'slot' => $slot->id]))
        ->assertOk()->assertSee('Confirm booking')->assertSee('Pentavalent-1');
});

test('the confirm page warns when the visit is too soon to change online', function () {
    $w = $this->world;
    $dose = ['doses' => [$w->dose('OPV-1')->id]];

    $this->actingAs($w->parent)
        ->get(route('parent.book.confirm', [$w->child, ...$dose, 'slot' => $w->slot(0, '15:00')->id]))
        ->assertOk()->assertSee("won't be able to cancel or change it online", false);

    $this->actingAs($w->parent)
        ->get(route('parent.book.confirm', [$w->child, ...$dose, 'slot' => $w->slot(3)->id]))
        ->assertOk()->assertDontSee("won't be able to cancel", false);
});

test('a full slot is not offered', function () {
    $w = $this->world;
    $full = $w->slot(3, '09:00', capacity: 1);
    $w->slot(3, '10:00');
    $w->book(['OPV-1'], $full, $w->childAged(49));

    $this->actingAs($w->parent)
        ->get(route('parent.book.slots', [$w->child, $w->hospital, 'doses' => [$w->dose('OPV-1')->id]]))
        ->assertOk()->assertDontSee('9:00 am')->assertSee('10:00 am');
});
