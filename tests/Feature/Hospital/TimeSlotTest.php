<?php

use App\Models\Hospital;
use App\Models\TimeSlot;

beforeEach(function () {
    // A Monday morning, so weekday maths is predictable.
    $this->travelTo('2026-03-02 07:00:00');
    $this->hospital = Hospital::factory()->active()->create();
});

function bulkPayload(array $overrides = []): array
{
    return [
        'date_from' => '2026-03-02',   // Mon
        'date_to' => '2026-03-08',     // Sun
        'weekdays' => [1, 2, 3, 4, 5], // Mon–Fri
        'start_time' => '09:00',
        'end_time' => '11:00',
        'slot_minutes' => 30,
        'capacity' => 5,
        ...$overrides,
    ];
}

test('a hospital adds a single slot', function () {
    $this->actingAs($this->hospital->user)
        ->post(route('hospital.slots.store'), ['date' => '2026-03-03', 'start_time' => '09:00', 'end_time' => '09:30', 'capacity' => 4, 'hospital_id' => 999])
        ->assertRedirect();

    $slot = TimeSlot::sole();
    expect($slot->hospital_id)->toBe($this->hospital->id)
        ->and($slot->start_time)->toBe('09:00:00')
        ->and($slot->capacity)->toBe(4);
});

test('single slots are validated', function (array $payload, string $field) {
    TimeSlot::factory()->create(['hospital_id' => $this->hospital->id, 'date' => '2026-03-03', 'start_time' => '10:00:00', 'end_time' => '10:30:00']);

    $this->actingAs($this->hospital->user)
        ->post(route('hospital.slots.store'), [...['date' => '2026-03-03', 'start_time' => '09:00', 'end_time' => '09:30', 'capacity' => 4], ...$payload])
        ->assertSessionHasErrors($field);

    expect(TimeSlot::count())->toBe(1);
})->with([
    'past day' => [['date' => '2026-03-01'], 'date'],
    'earlier today' => [['date' => '2026-03-02', 'start_time' => '06:00', 'end_time' => '06:30'], 'start_time'],
    'ends before it starts' => [['start_time' => '10:00', 'end_time' => '09:00'], 'end_time'],
    'overlaps an existing slot' => [['start_time' => '10:15', 'end_time' => '10:45'], 'start_time'],
    'zero capacity' => [['capacity' => 0], 'capacity'],
]);

test('a slot touching an existing one is fine', function () {
    TimeSlot::factory()->create(['hospital_id' => $this->hospital->id, 'date' => '2026-03-03', 'start_time' => '09:00:00', 'end_time' => '09:30:00']);

    $this->actingAs($this->hospital->user)
        ->post(route('hospital.slots.store'), ['date' => '2026-03-03', 'start_time' => '09:30', 'end_time' => '10:00', 'capacity' => 4])
        ->assertSessionHasNoErrors();
});

test('bulk create makes every slot on the chosen days', function () {
    $this->actingAs($this->hospital->user)
        ->post(route('hospital.slots.bulk.store'), bulkPayload())
        ->assertRedirect()
        ->assertSessionHas('success', 'Created 20 slots.');

    // 5 weekdays x 4 half-hour slots between 9 and 11.
    expect(TimeSlot::count())->toBe(20)
        ->and(TimeSlot::whereDate('date', '2026-03-07')->count())->toBe(0) // Saturday not chosen
        ->and(TimeSlot::where('capacity', 5)->count())->toBe(20)
        ->and(TimeSlot::where('hospital_id', '!=', $this->hospital->id)->count())->toBe(0);
});

test('bulk create skips slots that overlap existing ones', function () {
    TimeSlot::factory()->create(['hospital_id' => $this->hospital->id, 'date' => '2026-03-03', 'start_time' => '09:15:00', 'end_time' => '09:45:00']);
    // Another hospital's slot at the same time must not matter.
    TimeSlot::factory()->create(['date' => '2026-03-04', 'start_time' => '09:00:00', 'end_time' => '09:30:00']);

    $this->actingAs($this->hospital->user)
        ->post(route('hospital.slots.bulk.store'), bulkPayload())
        ->assertSessionHas('success', 'Created 18 slots. Skipped 2 that overlapped existing slots or had already passed.');

    expect($this->hospital->timeSlots()->count())->toBe(19);
});

test('running the same bulk create twice creates nothing the second time', function () {
    $this->actingAs($this->hospital->user)->post(route('hospital.slots.bulk.store'), bulkPayload());
    $this->actingAs($this->hospital->user)->post(route('hospital.slots.bulk.store'), bulkPayload())
        ->assertSessionHas('error');

    expect(TimeSlot::count())->toBe(20);
});

test('bulk create skips times that have already passed today', function () {
    $this->travelTo('2026-03-02 09:40:00');

    $this->actingAs($this->hospital->user)->post(route('hospital.slots.bulk.store'), bulkPayload());

    // Today's 9:00 and 9:30 slots are gone; 10:00 and 10:30 remain.
    expect(TimeSlot::whereDate('date', '2026-03-02')->pluck('start_time')->all())->toBe(['10:00:00', '10:30:00']);
});

test('bulk create is limited', function (array $overrides, string $field) {
    $this->actingAs($this->hospital->user)
        ->post(route('hospital.slots.bulk.store'), bulkPayload($overrides))
        ->assertSessionHasErrors($field);

    expect(TimeSlot::count())->toBe(0);
})->with([
    'range too long' => [['date_to' => '2026-06-30'], 'date_to'],
    'no weekdays' => [['weekdays' => []], 'weekdays'],
    'shorter than a slot' => [['end_time' => '09:15'], 'end_time'],
    'odd slot length' => [['slot_minutes' => 7], 'slot_minutes'],
    'starts in the past' => [['date_from' => '2026-03-01'], 'date_from'],
]);

test('bulk create refuses more than the configured maximum', function () {
    config(['vaccination.slots.bulk_max_slots' => 10]);

    $this->actingAs($this->hospital->user)
        ->post(route('hospital.slots.bulk.store'), bulkPayload())
        ->assertSessionHasErrors('date_to');

    expect(TimeSlot::count())->toBe(0);
});

test('a hospital changes a slot capacity and removes a slot', function () {
    $slot = TimeSlot::factory()->create(['hospital_id' => $this->hospital->id, 'date' => '2026-03-05']);

    $this->actingAs($this->hospital->user)->put(route('hospital.slots.update', $slot), ['capacity' => 9])->assertRedirect();
    expect($slot->fresh()->capacity)->toBe(9);

    $this->delete(route('hospital.slots.destroy', $slot))->assertRedirect();
    expect(TimeSlot::find($slot->id))->toBeNull();
});

test('past slots cannot be changed or removed', function () {
    $slot = TimeSlot::factory()->create(['hospital_id' => $this->hospital->id, 'date' => '2026-02-27', 'capacity' => 4]);

    $this->actingAs($this->hospital->user)->put(route('hospital.slots.update', $slot), ['capacity' => 9])->assertSessionHas('error');
    $this->delete(route('hospital.slots.destroy', $slot))->assertSessionHas('error');

    expect($slot->fresh()->capacity)->toBe(4);
});

test("a hospital cannot touch another hospital's slot", function (string $method, string $route) {
    $theirs = TimeSlot::factory()->create(['date' => '2026-03-05', 'capacity' => 4]);

    $this->actingAs($this->hospital->user)->call($method, route($route, $theirs), ['capacity' => 9])->assertForbidden();

    expect($theirs->fresh())->not->toBeNull()->and($theirs->fresh()->capacity)->toBe(4);
})->with([
    'edit form' => ['GET', 'hospital.slots.edit'],
    'update' => ['PUT', 'hospital.slots.update'],
    'delete' => ['DELETE', 'hospital.slots.destroy'],
]);

test('the slot list shows only my upcoming slots, grouped by day', function () {
    TimeSlot::factory()->create(['hospital_id' => $this->hospital->id, 'date' => '2026-03-04', 'start_time' => '09:00:00', 'end_time' => '09:30:00']);
    TimeSlot::factory()->create(['hospital_id' => $this->hospital->id, 'date' => '2026-02-20']); // past
    TimeSlot::factory()->create(['date' => '2026-03-04', 'start_time' => '15:00:00', 'end_time' => '15:30:00']); // someone else's

    $this->actingAs($this->hospital->user)->get(route('hospital.slots.index'))
        ->assertOk()
        ->assertSee('Wednesday 4 March 2026')
        ->assertSee('9:00 am – 9:30 am')
        ->assertDontSee('3:00 pm')
        ->assertDontSee('20 February');
});
