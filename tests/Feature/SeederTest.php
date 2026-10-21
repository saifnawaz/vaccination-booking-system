<?php

use App\Enums\DoseStatus;
use App\Enums\HospitalStatus;
use App\Enums\Role;
use App\Models\Child;
use App\Models\Hospital;
use App\Models\HospitalStock;
use App\Models\TimeSlot;
use App\Models\User;
use App\Models\VaccineSchedule;
use App\Services\StockSummary;
use Database\Seeders\DatabaseSeeder;

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
});

test('the seeder creates the demo accounts', function () {
    expect(User::where('role', Role::Admin)->count())->toBe(1)
        ->and(User::where('role', Role::Hospital)->count())->toBe(3)
        ->and(User::where('role', Role::Parent)->count())->toBe(10)
        ->and(Hospital::where('status', HospitalStatus::Active)->count())->toBe(2)
        ->and(Hospital::where('status', HospitalStatus::Inactive)->count())->toBe(1);
});

test('seed data never uses real email addresses', function () {
    User::all()->each(fn (User $user) => expect($user->email)->toEndWith('@test.com'));
});

test('the seeded admin can log in and the inactive hospital cannot', function () {
    $this->post('/login', ['email' => 'admin@test.com', 'password' => 'password']);
    $this->assertAuthenticated();
    auth()->logout();

    $inactive = Hospital::where('status', HospitalStatus::Inactive)->sole();
    $this->post('/login', ['email' => $inactive->user->email, 'password' => 'password']);
    $this->assertGuest();
});

test('about 20 children are seeded, each with a full schedule', function () {
    expect(Child::count())->toBe(20);

    Child::all()->each(fn (Child $child) => expect($child->doses()->count())->toBe(VaccineSchedule::count()));
});

test('every schedule state appears in the seed data', function () {
    $statuses = Child::all()
        ->flatMap(fn (Child $child) => $child->schedule()->doses())
        ->map(fn ($dose) => $dose->status)
        ->unique();

    foreach (DoseStatus::cases() as $status) {
        expect($statuses->contains($status))->toBeTrue("No seeded dose is {$status->value}");
    }
});

test('the seed stock includes an expired batch and a low-stock vaccine', function () {
    expect(HospitalStock::whereDate('expiry_date', '<', today())->where('quantity', '>', 0)->count())->toBeGreaterThanOrEqual(1);

    $lowSomewhere = Hospital::where('status', HospitalStatus::Active)->get()
        ->contains(fn (Hospital $hospital) => (new StockSummary($hospital))->lowStockCount() > 0);

    expect($lowSomewhere)->toBeTrue();
});

test('seeded doses only use batches that had not expired', function () {
    Child::all()->flatMap->doses
        ->filter->hospital_stock_id
        ->each(fn ($dose) => expect($dose->hospitalStock->expiry_date->gte($dose->administered_at))->toBeTrue());
});

test('active hospitals have upcoming slots and none are in the past', function () {
    Hospital::where('status', HospitalStatus::Active)->get()
        ->each(fn (Hospital $hospital) => expect($hospital->timeSlots()->count())->toBeGreaterThan(0));

    expect(TimeSlot::whereDate('date', '<', today())->count())->toBe(0);
});
