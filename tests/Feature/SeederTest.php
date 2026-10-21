<?php

use App\Enums\HospitalStatus;
use App\Enums\Role;
use App\Models\Hospital;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;

test('the seeder creates the demo accounts', function () {
    $this->seed(DatabaseSeeder::class);

    expect(User::where('role', Role::Admin)->count())->toBe(1)
        ->and(User::where('role', Role::Hospital)->count())->toBe(3)
        ->and(User::where('role', Role::Parent)->count())->toBe(10)
        ->and(Hospital::where('status', HospitalStatus::Active)->count())->toBe(2)
        ->and(Hospital::where('status', HospitalStatus::Inactive)->count())->toBe(1);
});

test('seed data never uses real email addresses', function () {
    $this->seed(DatabaseSeeder::class);

    User::all()->each(fn (User $user) => expect($user->email)->toEndWith('@test.com'));
});

test('the seeded admin can log in and the inactive hospital cannot', function () {
    $this->seed(DatabaseSeeder::class);

    $this->post('/login', ['email' => 'admin@test.com', 'password' => 'password']);
    $this->assertAuthenticated();
    auth()->logout();

    $inactive = Hospital::where('status', HospitalStatus::Inactive)->sole();
    $this->post('/login', ['email' => $inactive->user->email, 'password' => 'password']);
    $this->assertGuest();
});
