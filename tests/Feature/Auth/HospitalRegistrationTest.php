<?php

use App\Enums\HospitalStatus;
use App\Enums\Role;
use App\Models\Hospital;
use App\Models\User;

function hospitalRegistration(array $overrides = []): array
{
    return [
        'hospital_name' => 'Example Children\'s Clinic',
        'address' => '1 Example Road',
        'city' => 'Lahore',
        'hospital_phone' => '042-0000000',
        'license_number' => 'LIC-0001',
        'name' => 'Example Manager',
        'email' => 'clinic@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        ...$overrides,
    ];
}

test('hospital registration screen can be rendered', function () {
    $this->get('/register/hospital')->assertOk()->assertSee('Register your hospital');
});

test('a hospital registers as pending and is not logged in', function () {
    $response = $this->post('/register/hospital', hospitalRegistration());

    $response->assertRedirect(route('register.hospital.pending'));
    $this->assertGuest();

    $user = User::where('email', 'clinic@example.com')->sole();
    expect($user->role)->toBe(Role::Hospital);

    $hospital = $user->hospital;
    expect($hospital)->not->toBeNull()
        ->and($hospital->name)->toBe('Example Children\'s Clinic')
        ->and($hospital->status)->toBe(HospitalStatus::Pending)
        ->and($hospital->approved_by)->toBeNull()
        ->and($hospital->approved_at)->toBeNull();
});

test('a hospital cannot register itself as already active', function () {
    $this->post('/register/hospital', hospitalRegistration([
        'status' => 'active',
        'approved_by' => 1,
    ]));

    $hospital = Hospital::sole();
    expect($hospital->status)->toBe(HospitalStatus::Pending)
        ->and($hospital->approved_by)->toBeNull();
});

test('hospital registration validates its fields', function () {
    $this->post('/register/hospital', hospitalRegistration([
        'hospital_name' => '',
        'city' => '',
        'hospital_phone' => 'not a phone',
        'email' => 'not-an-email',
    ]))->assertSessionHasErrors(['hospital_name', 'city', 'hospital_phone', 'email']);

    expect(User::count())->toBe(0)->and(Hospital::count())->toBe(0);
});

test('the awaiting approval page can be rendered', function () {
    $this->get('/register/hospital/pending')->assertOk()->assertSee('Registration received');
});
