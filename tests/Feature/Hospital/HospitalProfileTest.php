<?php

use App\Models\Hospital;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

test('a hospital can update its own profile', function () {
    $hospital = Hospital::factory()->active()->create();

    $this->actingAs($hospital->user)
        ->put(route('hospital.profile.update'), [
            'hospital_name' => 'Renamed Clinic',
            'address' => '2 New Road',
            'city' => 'Multan',
            'hospital_phone' => '061-0000000',
            'license_number' => '',
        ])
        ->assertRedirect(route('hospital.profile.edit'))
        ->assertSessionHas('success');

    $hospital->refresh();
    expect($hospital->name)->toBe('Renamed Clinic')
        ->and($hospital->city)->toBe('Multan')
        ->and($hospital->license_number)->toBeNull();
});

test('a hospital profile update cannot change its status', function () {
    $hospital = Hospital::factory()->active()->create();

    $this->actingAs($hospital->user)->put(route('hospital.profile.update'), [
        'hospital_name' => 'Same',
        'address' => 'Same',
        'city' => 'Same',
        'hospital_phone' => '061-0000000',
        'status' => 'inactive',
        'user_id' => 999,
    ]);

    $hospital->refresh();
    expect($hospital->isActive())->toBeTrue()
        ->and($hospital->user_id)->not->toBe(999);
});

test('the hospital policy only lets a hospital edit its own profile', function () {
    $mine = Hospital::factory()->active()->create();
    $theirs = Hospital::factory()->active()->create();
    $admin = User::factory()->admin()->create();
    $parent = User::factory()->parent()->create();

    expect(Gate::forUser($mine->user)->allows('update', $mine))->toBeTrue()
        ->and(Gate::forUser($mine->user)->allows('update', $theirs))->toBeFalse()
        ->and(Gate::forUser($mine->user)->allows('view', $theirs))->toBeFalse()
        ->and(Gate::forUser($mine->user)->allows('changeStatus', $mine))->toBeFalse()
        ->and(Gate::forUser($admin)->allows('view', $theirs))->toBeTrue()
        ->and(Gate::forUser($admin)->allows('changeStatus', $theirs))->toBeTrue()
        ->and(Gate::forUser($parent)->allows('view', $theirs))->toBeFalse();
});

test('a hospital cannot open the admin page of another hospital', function () {
    $mine = Hospital::factory()->active()->create();
    $theirs = Hospital::factory()->active()->create();

    $this->actingAs($mine->user)->get(route('admin.hospitals.show', $theirs))->assertForbidden();
});
