<?php

use App\Enums\Role;
use App\Models\Hospital;
use App\Models\User;

function newAdminPayload(): array
{
    return [
        'name' => 'Second Admin',
        'email' => 'second-admin@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ];
}

test('an admin can create another admin', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->post(route('admin.admins.store'), newAdminPayload())
        ->assertRedirect(route('admin.admins.index'));

    expect(User::where('email', 'second-admin@example.com')->sole()->role)->toBe(Role::Admin);
});

test('only admins can create admins', function (string $role) {
    $user = $role === 'hospital'
        ? Hospital::factory()->active()->create()->user
        : User::factory()->parent()->create();

    $this->actingAs($user)->post(route('admin.admins.store'), newAdminPayload())->assertForbidden();

    expect(User::where('email', 'second-admin@example.com')->exists())->toBeFalse();
})->with(['parent', 'hospital']);

test('admin creation validates input', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->post(route('admin.admins.store'), [...newAdminPayload(), 'email' => $admin->email, 'password_confirmation' => 'different'])
        ->assertSessionHasErrors(['email', 'password']);
});

test('the administrators list shows admins only', function () {
    $admin = User::factory()->admin()->create(['name' => 'Alice Admin']);
    User::factory()->parent()->create(['name' => 'Paula Parent']);

    $this->actingAs($admin)->get(route('admin.admins.index'))
        ->assertOk()->assertSee('Alice Admin')->assertDontSee('Paula Parent');
});
