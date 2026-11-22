<?php

use App\Models\ChildDose;
use App\Models\Hospital;
use App\Models\HospitalStock;
use App\Models\Vaccine;
use App\Services\StockSummary;

beforeEach(function () {
    $this->hospital = Hospital::factory()->active()->create();
    $this->vaccine = Vaccine::factory()->create(['name' => 'Polio test', 'code' => 'POLIO']);
});

function batchPayload(Vaccine $vaccine, array $overrides = []): array
{
    return [
        'vaccine_id' => $vaccine->id,
        'batch_number' => 'ab-123',
        'expiry_date' => now()->addMonths(3)->toDateString(),
        'quantity' => 40,
        ...$overrides,
    ];
}

test('a hospital adds a batch to its own stock', function () {
    $this->actingAs($this->hospital->user)
        ->post(route('hospital.stock.store'), batchPayload($this->vaccine, ['hospital_id' => 999, 'reserved' => 30]))
        ->assertRedirect(route('hospital.stock.index'));

    $batch = HospitalStock::sole();
    expect($batch->hospital_id)->toBe($this->hospital->id)
        ->and($batch->batch_number)->toBe('AB-123')
        ->and($batch->quantity)->toBe(40)
        ->and($batch->reserved)->toBe(0);
});

test('expired, inactive and duplicate batches are refused', function (Closure $payload, string $field) {
    HospitalStock::factory()->create(['hospital_id' => $this->hospital->id, 'vaccine_id' => $this->vaccine->id, 'batch_number' => 'DUP-1']);

    $this->actingAs($this->hospital->user)
        ->post(route('hospital.stock.store'), $payload($this->vaccine))
        ->assertSessionHasErrors($field);

    expect(HospitalStock::count())->toBe(1);
})->with([
    'expired' => [fn (Vaccine $v) => batchPayload($v, ['expiry_date' => now()->toDateString()]), 'expiry_date'],
    'inactive vaccine' => [fn () => batchPayload(Vaccine::factory()->inactive()->create()), 'vaccine_id'],
    'deleted vaccine' => [fn () => batchPayload(tap(Vaccine::factory()->create())->delete()), 'vaccine_id'],
    'duplicate batch' => [fn (Vaccine $v) => batchPayload($v, ['batch_number' => 'dup-1']), 'batch_number'],
    'zero doses' => [fn (Vaccine $v) => batchPayload($v, ['quantity' => 0]), 'quantity'],
]);

test('two hospitals may use the same batch number', function () {
    HospitalStock::factory()->create(['vaccine_id' => $this->vaccine->id, 'batch_number' => 'AB-123']);

    $this->actingAs($this->hospital->user)
        ->post(route('hospital.stock.store'), batchPayload($this->vaccine))
        ->assertSessionHasNoErrors();

    expect(HospitalStock::count())->toBe(2);
});

test('a hospital adjusts a batch but never below the reserved doses', function () {
    $batch = HospitalStock::factory()->create(['hospital_id' => $this->hospital->id, 'quantity' => 50, 'reserved' => 5]);

    $this->actingAs($this->hospital->user)->put(route('hospital.stock.update', $batch), ['quantity' => 30])
        ->assertRedirect(route('hospital.stock.index'));
    expect($batch->fresh()->quantity)->toBe(30);

    $this->put(route('hospital.stock.update', $batch), ['quantity' => 4])->assertSessionHasErrors('quantity');
    expect($batch->fresh()->quantity)->toBe(30);
});

test("a hospital cannot touch another hospital's batch", function (string $method, string $route) {
    $theirs = HospitalStock::factory()->create(['quantity' => 50]);

    $this->actingAs($this->hospital->user)
        ->call($method, route($route, $theirs), ['quantity' => 1])
        ->assertForbidden();

    expect($theirs->fresh())->not->toBeNull()->and($theirs->fresh()->quantity)->toBe(50);
})->with([
    'edit form' => ['GET', 'hospital.stock.edit'],
    'adjust' => ['PUT', 'hospital.stock.update'],
    'remove' => ['DELETE', 'hospital.stock.destroy'],
]);

test('an unused batch can be removed', function () {
    $batch = HospitalStock::factory()->create(['hospital_id' => $this->hospital->id]);

    $this->actingAs($this->hospital->user)->delete(route('hospital.stock.destroy', $batch))
        ->assertRedirect(route('hospital.stock.index'));

    expect(HospitalStock::find($batch->id))->toBeNull();
});

test('a batch that was used or reserved cannot be removed', function (string $why) {
    $batch = HospitalStock::factory()->create(['hospital_id' => $this->hospital->id, 'reserved' => $why === 'reserved' ? 1 : 0]);

    if ($why === 'used') {
        $dose = ChildDose::factory()->create(['administered_at' => now()->subDay(), 'hospital_stock_id' => $batch->id]);
    }

    $this->actingAs($this->hospital->user)->delete(route('hospital.stock.destroy', $batch))
        ->assertSessionHas('error');

    expect(HospitalStock::find($batch->id))->not->toBeNull();
})->with(['used', 'reserved']);

test('the stock summary ignores expired batches and flags low stock', function () {
    $other = Vaccine::factory()->create(['name' => 'Measles test']);
    HospitalStock::factory()->create(['hospital_id' => $this->hospital->id, 'vaccine_id' => $this->vaccine->id, 'quantity' => 8, 'reserved' => 2]);
    HospitalStock::factory()->expired()->create(['hospital_id' => $this->hospital->id, 'vaccine_id' => $this->vaccine->id, 'quantity' => 100]);
    HospitalStock::factory()->expiringSoon()->create(['hospital_id' => $this->hospital->id, 'vaccine_id' => $other->id, 'quantity' => 40]);

    $summary = new StockSummary($this->hospital);
    $polio = $summary->vaccines->firstWhere('vaccine.id', $this->vaccine->id);
    $measles = $summary->vaccines->firstWhere('vaccine.id', $other->id);

    expect($polio['usable'])->toBe(6)       // 8 - 2 reserved; the expired 100 don't count
        ->and($polio['low'])->toBeTrue()
        ->and($polio['expired'])->toBe(1)
        ->and($measles['usable'])->toBe(40)
        ->and($measles['low'])->toBeFalse()
        ->and($measles['expiring'])->toBe(1)
        ->and($summary->usableDoses())->toBe(46)
        ->and($summary->lowStockCount())->toBe(1);
});

test('the stock page shows warnings and can be filtered', function () {
    HospitalStock::factory()->expired()->create(['hospital_id' => $this->hospital->id, 'vaccine_id' => $this->vaccine->id, 'batch_number' => 'OLD-1']);
    HospitalStock::factory()->create(['hospital_id' => $this->hospital->id, 'vaccine_id' => $this->vaccine->id, 'batch_number' => 'NEW-1', 'quantity' => 3]);
    HospitalStock::factory()->create(['batch_number' => 'OTHER-HOSPITAL']);

    $this->actingAs($this->hospital->user);

    $this->get(route('hospital.stock.index'))->assertOk()
        ->assertSee('OLD-1')->assertSee('NEW-1')->assertSee('Expired')->assertSee('Low stock')
        ->assertDontSee('OTHER-HOSPITAL');

    $this->get(route('hospital.stock.index', ['condition' => 'expired']))->assertSee('OLD-1')->assertDontSee('NEW-1');
    $this->get(route('hospital.stock.index', ['condition' => 'usable']))->assertSee('NEW-1')->assertDontSee('OLD-1');
});

test('the hospital dashboard shows real stock numbers', function () {
    HospitalStock::factory()->create(['hospital_id' => $this->hospital->id, 'vaccine_id' => $this->vaccine->id, 'quantity' => 30]);

    $response = $this->actingAs($this->hospital->user)->get(route('hospital.dashboard'))->assertOk();

    expect($response->viewData('stock')->usableDoses())->toBe(30)
        ->and($response->viewData('upcomingSlotCount'))->toBe(0);
    $response->assertSee('No upcoming time slots');
});
