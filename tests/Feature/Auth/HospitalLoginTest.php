<?php

use App\Enums\HospitalStatus;
use App\Models\Hospital;
use App\Models\User;

test('an active hospital can log in', function () {
    $hospital = Hospital::factory()->active()->create();

    $this->post('/login', ['email' => $hospital->user->email, 'password' => 'password'])
        ->assertRedirect(route('dashboard', absolute: false));

    $this->assertAuthenticatedAs($hospital->user);
    $this->get('/dashboard')->assertRedirect(route('hospital.dashboard'));
});

test('a pending hospital cannot log in until approved', function () {
    $hospital = Hospital::factory()->create();

    $this->post('/login', ['email' => $hospital->user->email, 'password' => 'password'])
        ->assertSessionHasErrors(['email' => __('Your hospital is awaiting approval by an administrator. You can sign in once it has been approved.')]);

    $this->assertGuest();
});

test('an inactive hospital cannot log in', function () {
    $hospital = Hospital::factory()->inactive()->create();

    $this->post('/login', ['email' => $hospital->user->email, 'password' => 'password'])
        ->assertSessionHasErrors(['email' => __('This hospital account is inactive. Please contact the administrator.')]);

    $this->assertGuest();
});

test('a wrong password does not reveal that a hospital is pending', function () {
    $hospital = Hospital::factory()->create();

    $this->post('/login', ['email' => $hospital->user->email, 'password' => 'wrong-password'])
        ->assertSessionHasErrors(['email' => trans('auth.failed')]);

    $this->assertGuest();
});

test('a hospital deactivated mid-session is signed out on its next request', function () {
    $hospital = Hospital::factory()->active()->create();
    $admin = User::factory()->admin()->create();

    $this->actingAs($hospital->user)->get(route('hospital.dashboard'))->assertOk();

    $hospital->transitionTo(HospitalStatus::Inactive, $admin);

    $this->get(route('hospital.dashboard'))->assertRedirect(route('login'));
    $this->assertGuest();
});

test('a hospital login without a hospital profile is blocked', function () {
    $user = User::factory()->hospital()->create();

    $this->post('/login', ['email' => $user->email, 'password' => 'password']);

    $this->assertGuest();
});
