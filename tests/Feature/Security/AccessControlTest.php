<?php

use App\Models\Hospital;
use App\Models\User;

/**
 * Route access by role. Every protected area is checked for guests and for
 * each role that should not be there.
 */
function userWithRole(string $role): User
{
    return match ($role) {
        'admin' => User::factory()->admin()->create(),
        'hospital' => Hospital::factory()->active()->create()->user,
        'parent' => User::factory()->parent()->create(),
    };
}

dataset('protected pages', [
    'dashboard' => ['/dashboard'],
    'account settings' => ['/profile'],
    'admin overview' => ['/admin'],
    'admin hospitals' => ['/admin/hospitals'],
    'admin administrators' => ['/admin/admins'],
    'admin add administrator' => ['/admin/admins/create'],
    'hospital overview' => ['/hospital'],
    'hospital profile' => ['/hospital/profile'],
    'parent home' => ['/parent'],
]);

test('guests are redirected to log in', function (string $url) {
    $this->get($url)->assertRedirect(route('login'));
})->with('protected pages');

test('guests cannot change data either', function () {
    $hospital = Hospital::factory()->create();

    $this->patch(route('admin.hospitals.status', $hospital), ['status' => 'active'])->assertRedirect(route('login'));
    $this->post(route('admin.admins.store'), [
        'name' => 'X', 'email' => 'x@example.com', 'password' => 'password', 'password_confirmation' => 'password',
    ])->assertRedirect(route('login'));

    expect($hospital->fresh()->isActive())->toBeFalse()
        ->and(User::where('email', 'x@example.com')->exists())->toBeFalse();
});

test('each area is closed to the other roles', function (string $role, string $url) {
    $this->actingAs(userWithRole($role))->get($url)->assertForbidden();
})->with([
    'parent → admin' => ['parent', '/admin'],
    'parent → admin hospitals' => ['parent', '/admin/hospitals'],
    'parent → admin admins' => ['parent', '/admin/admins'],
    'parent → hospital' => ['parent', '/hospital'],
    'parent → hospital profile' => ['parent', '/hospital/profile'],
    'hospital → admin' => ['hospital', '/admin'],
    'hospital → admin hospitals' => ['hospital', '/admin/hospitals'],
    'hospital → admin admins' => ['hospital', '/admin/admins/create'],
    'hospital → parent' => ['hospital', '/parent'],
    'admin → hospital' => ['admin', '/hospital'],
    'admin → parent' => ['admin', '/parent'],
]);

test('each role lands on its own home', function (string $role, string $home) {
    $this->actingAs(userWithRole($role))->get('/dashboard')->assertRedirect(route($home));
})->with([
    ['admin', 'admin.dashboard'],
    ['hospital', 'hospital.dashboard'],
    ['parent', 'parent.dashboard'],
]);

test('every page renders for its own role', function (string $role, string $url) {
    $this->actingAs(userWithRole($role))->get($url)->assertOk();
})->with([
    ['admin', '/admin'],
    ['admin', '/admin/hospitals'],
    ['admin', '/admin/admins'],
    ['admin', '/admin/admins/create'],
    ['admin', '/profile'],
    ['hospital', '/hospital'],
    ['hospital', '/hospital/profile'],
    ['hospital', '/profile'],
    ['parent', '/parent'],
    ['parent', '/profile'],
]);

test('public pages render for guests', function (string $url) {
    $this->get($url)->assertOk();
})->with(['/', '/login', '/register', '/register/hospital', '/register/hospital/pending', '/forgot-password']);
