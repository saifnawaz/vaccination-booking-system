<?php

use App\Enums\Role;
use App\Models\User;

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response->assertStatus(200);
});

test('new users can register', function () {
    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
});

test('public registration always creates a parent', function () {
    $this->post('/register', [
        'name' => 'Test Parent',
        'email' => 'parent@example.com',
        'phone' => '0300-0000000',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $user = User::where('email', 'parent@example.com')->sole();

    expect($user->role)->toBe(Role::Parent)
        ->and($user->phone)->toBe('0300-0000000');
});

test('the public cannot create an admin by posting a role', function (string $role) {
    $this->post('/register', [
        'name' => 'Sneaky',
        'email' => 'sneaky@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'role' => $role,
    ]);

    expect(User::where('email', 'sneaky@example.com')->sole()->role)->toBe(Role::Parent)
        ->and(User::where('role', Role::Admin)->exists())->toBeFalse();
})->with(['admin', 'hospital']);

test('a new parent lands on the parent home', function () {
    $this->post('/register', [
        'name' => 'Test Parent',
        'email' => 'parent@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->get('/dashboard')->assertRedirect(route('parent.dashboard'));
});
