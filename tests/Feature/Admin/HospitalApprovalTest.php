<?php

use App\Enums\HospitalStatus;
use App\Models\Hospital;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
});

test('an admin approves a pending hospital, recording who and when', function () {
    $hospital = Hospital::factory()->create();

    $this->actingAs($this->admin)
        ->patch(route('admin.hospitals.status', $hospital), ['status' => 'active'])
        ->assertRedirect()
        ->assertSessionHas('success', "{$hospital->name} has been approved.");

    $hospital->refresh();
    expect($hospital->status)->toBe(HospitalStatus::Active)
        ->and($hospital->approved_by)->toBe($this->admin->id)
        ->and($hospital->approved_at)->not->toBeNull();
});

test('an approved hospital can then log in', function () {
    $hospital = Hospital::factory()->create();

    $this->actingAs($this->admin)->patch(route('admin.hospitals.status', $hospital), ['status' => 'active']);
    auth()->logout();

    $this->post('/login', ['email' => $hospital->user->email, 'password' => 'password']);
    $this->assertAuthenticatedAs($hospital->user);
});

test('an admin can decline, deactivate and reactivate', function (string $from, string $to, string $verb) {
    $hospital = Hospital::factory()->state(['status' => $from])->create();

    $this->actingAs($this->admin)
        ->patch(route('admin.hospitals.status', $hospital), ['status' => $to])
        ->assertSessionHas('success', "{$hospital->name} has been {$verb}.");

    expect($hospital->fresh()->status->value)->toBe($to);
})->with([
    'decline' => ['pending', 'inactive', 'declined'],
    'deactivate' => ['active', 'inactive', 'deactivated'],
    'reactivate' => ['inactive', 'active', 'reactivated'],
]);

test('invalid status changes are refused', function (string $from, string $to) {
    $hospital = Hospital::factory()->state(['status' => $from])->create();

    $this->actingAs($this->admin)
        ->patch(route('admin.hospitals.status', $hospital), ['status' => $to])
        ->assertSessionHasErrors('status');

    expect($hospital->fresh()->status->value)->toBe($from);
})->with([
    'active → active' => ['active', 'active'],
    'inactive → inactive' => ['inactive', 'inactive'],
    'anything → pending' => ['active', 'pending'],
    'unknown status' => ['pending', 'deleted'],
]);

test('reactivating keeps the original approval record', function () {
    $hospital = Hospital::factory()->create();
    $this->actingAs($this->admin)->patch(route('admin.hospitals.status', $hospital), ['status' => 'active']);
    $firstApproval = $hospital->fresh()->approved_at;

    $other = User::factory()->admin()->create();
    $this->actingAs($other)->patch(route('admin.hospitals.status', $hospital), ['status' => 'inactive']);
    $this->actingAs($other)->patch(route('admin.hospitals.status', $hospital), ['status' => 'active']);

    $hospital->refresh();
    expect($hospital->approved_by)->toBe($this->admin->id)
        ->and($hospital->approved_at->equalTo($firstApproval))->toBeTrue();
});

test('a hospital cannot approve itself or change another hospital', function () {
    $pending = Hospital::factory()->create();
    $active = Hospital::factory()->active()->create();
    $other = Hospital::factory()->active()->create();

    $this->actingAs($active->user)
        ->patch(route('admin.hospitals.status', $other), ['status' => 'inactive'])
        ->assertForbidden();
    $this->actingAs($active->user)
        ->patch(route('admin.hospitals.status', $pending), ['status' => 'active'])
        ->assertForbidden();

    expect($other->fresh()->isActive())->toBeTrue()
        ->and($pending->fresh()->status)->toBe(HospitalStatus::Pending);
});

test('a parent cannot change a hospital status', function () {
    $hospital = Hospital::factory()->create();

    $this->actingAs(User::factory()->parent()->create())
        ->patch(route('admin.hospitals.status', $hospital), ['status' => 'active'])
        ->assertForbidden();

    expect($hospital->fresh()->status)->toBe(HospitalStatus::Pending);
});

test('the hospital list filters by status and searches', function () {
    Hospital::factory()->create(['name' => 'Alpha Clinic', 'city' => 'Lahore']);
    Hospital::factory()->active()->create(['name' => 'Beta Clinic', 'city' => 'Karachi']);
    Hospital::factory()->inactive()->create(['name' => 'Gamma Clinic', 'city' => 'Lahore']);

    $this->actingAs($this->admin);

    $this->get(route('admin.hospitals.index', ['status' => 'pending']))
        ->assertOk()->assertSee('Alpha Clinic')->assertDontSee('Beta Clinic')->assertDontSee('Gamma Clinic');

    $this->get(route('admin.hospitals.index', ['search' => 'Lahore']))
        ->assertOk()->assertSee('Alpha Clinic')->assertSee('Gamma Clinic')->assertDontSee('Beta Clinic');

    $this->get(route('admin.hospitals.index', ['search' => 'nothing-matches']))
        ->assertOk()->assertSee('No hospitals match');
});

test('the hospital list is paginated', function () {
    Hospital::factory()->count(16)->create();

    $this->actingAs($this->admin)->get(route('admin.hospitals.index'))
        ->assertOk()->assertSee('Showing')->assertSee('of');
    $this->actingAs($this->admin)->get(route('admin.hospitals.index', ['page' => 2]))->assertOk();
});

test('an admin can view a hospital', function () {
    $hospital = Hospital::factory()->create(['name' => 'Delta Clinic']);

    $this->actingAs($this->admin)->get(route('admin.hospitals.show', $hospital))
        ->assertOk()->assertSee('Delta Clinic')->assertSee('Approve')->assertSee('Decline');
});

test('the admin dashboard shows real counts', function () {
    Hospital::factory()->count(2)->create();
    Hospital::factory()->count(3)->active()->create();
    User::factory()->count(4)->parent()->create();

    $this->actingAs($this->admin)->get(route('admin.dashboard'))
        ->assertOk()
        ->assertViewHas('pendingHospitals', 2)
        ->assertViewHas('activeHospitals', 3)
        ->assertViewHas('inactiveHospitals', 0)
        ->assertViewHas('parents', 4);
});
