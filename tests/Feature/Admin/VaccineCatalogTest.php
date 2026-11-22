<?php

use App\Models\Hospital;
use App\Models\User;
use App\Models\Vaccine;
use App\Models\VaccineSchedule;
use Database\Seeders\EpiScheduleSeeder;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
});

test('an admin adds a vaccine, with the code normalised to capitals', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.vaccines.store'), ['name' => 'Influenza', 'code' => ' flu-1 ', 'description' => 'Seasonal', 'is_active' => '1'])
        ->assertRedirect(route('admin.vaccines.index'));

    $vaccine = Vaccine::sole();
    expect($vaccine->code)->toBe('FLU-1')->and($vaccine->is_active)->toBeTrue();
});

test('vaccine codes are unique, including deleted vaccines', function () {
    Vaccine::factory()->create(['code' => 'FLU'])->delete();

    $this->actingAs($this->admin)
        ->post(route('admin.vaccines.store'), ['name' => 'Flu again', 'code' => 'flu'])
        ->assertSessionHasErrors('code');
});

test('an admin can edit and deactivate a vaccine', function () {
    $vaccine = Vaccine::factory()->create(['code' => 'FLU']);

    $this->actingAs($this->admin)
        ->put(route('admin.vaccines.update', $vaccine), ['name' => 'Influenza', 'code' => 'FLU', 'is_active' => '0'])
        ->assertRedirect(route('admin.vaccines.index'));

    expect($vaccine->fresh()->name)->toBe('Influenza')->and($vaccine->fresh()->is_active)->toBeFalse();
});

test('a vaccine in the routine schedule cannot be deleted', function () {
    $this->seed(EpiScheduleSeeder::class);
    $bcg = Vaccine::where('code', 'BCG')->sole();

    $this->actingAs($this->admin)->delete(route('admin.vaccines.destroy', $bcg))
        ->assertSessionHas('error');

    $this->assertNotSoftDeleted($bcg);
});

test('an unscheduled vaccine is soft-deleted', function () {
    $vaccine = Vaccine::factory()->create();

    $this->actingAs($this->admin)->delete(route('admin.vaccines.destroy', $vaccine))
        ->assertRedirect(route('admin.vaccines.index'));

    $this->assertSoftDeleted($vaccine);
});

test('only admins manage the catalog', function (string $role) {
    $user = $role === 'parent' ? User::factory()->parent()->create() : Hospital::factory()->active()->create()->user;
    $vaccine = Vaccine::factory()->create();

    $this->actingAs($user)->get(route('admin.vaccines.index'))->assertForbidden();
    $this->actingAs($user)->post(route('admin.vaccines.store'), ['name' => 'X', 'code' => 'X'])->assertForbidden();
    $this->actingAs($user)->delete(route('admin.vaccines.destroy', $vaccine))->assertForbidden();

    $this->assertNotSoftDeleted($vaccine);
})->with(['parent', 'hospital']);

test('the catalog can be searched and filtered', function () {
    Vaccine::factory()->create(['name' => 'Alpha vaccine', 'code' => 'ALPHA']);
    Vaccine::factory()->inactive()->create(['name' => 'Beta vaccine', 'code' => 'BETA']);

    $this->actingAs($this->admin)->get(route('admin.vaccines.index', ['search' => 'alp']))
        ->assertOk()->assertSee('Alpha vaccine')->assertDontSee('Beta vaccine');
    $this->actingAs($this->admin)->get(route('admin.vaccines.index', ['status' => 'inactive']))
        ->assertOk()->assertSee('Beta vaccine')->assertDontSee('Alpha vaccine');
});

test('the routine schedule page lists every dose and the verification warning', function () {
    $this->seed(EpiScheduleSeeder::class);

    $response = $this->actingAs($this->admin)->get(route('admin.vaccines.schedule'))->assertOk();

    $response->assertSee('Must be verified before real use');
    VaccineSchedule::all()->each(fn ($dose) => $response->assertSee($dose->label));
});

test('re-running the schedule seeder updates rather than duplicates', function () {
    $this->seed(EpiScheduleSeeder::class);
    $counts = [Vaccine::count(), VaccineSchedule::count()];

    $this->seed(EpiScheduleSeeder::class);

    expect([Vaccine::count(), VaccineSchedule::count()])->toBe($counts);
});
