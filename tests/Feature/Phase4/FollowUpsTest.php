<?php

use App\Enums\AppointmentStatus;
use App\Enums\HospitalStatus;
use App\Exceptions\BookingException;
use App\Models\ActivityLog;
use App\Models\User;
use App\Notifications\AppointmentUpdated;
use App\Services\AppointmentService;
use Illuminate\Console\Scheduling\Schedule;
use Tests\Support\BookingWorld;

beforeEach(function () {
    $this->travelTo('2026-03-02 08:00:00');
    $this->world = BookingWorld::create(childAgeDays: 49);
});

// 1. Slots within 2 hours ------------------------------------------------------

test('slots starting within 2 hours are hidden and refused', function () {
    $w = $this->world;
    $soon = $w->slot(0, '09:30'); // 1h30 away
    $later = $w->slot(0, '10:30'); // 2h30 away

    $this->actingAs($w->parent)
        ->get(route('parent.book.slots', [$w->child, $w->hospital, 'doses' => [$w->dose('OPV-1')->id]]))
        ->assertOk()->assertDontSee('9:30 am')->assertSee('10:30 am');

    expect(fn () => $w->book(['OPV-1'], $soon))->toThrow(BookingException::class, 'too soon');
    expect($w->book(['OPV-1'], $later))->toHaveCount(1);
});

test('the minimum notice is configurable', function () {
    config(['vaccination.booking.min_notice_minutes' => 0]);
    $w = $this->world;

    expect($w->book(['OPV-1'], $w->slot(0, '09:30')))->toHaveCount(1);
});

// 2. Deactivating a hospital ---------------------------------------------------

test('deactivating a hospital cancels its open appointments, releases stock, notifies and logs', function () {
    $w = $this->world;
    $admin = User::factory()->admin()->create();
    $pending = $w->book(['OPV-1', 'PCV-1'], $w->slot(3));
    $approved = $w->service->approve($w->book(['Rota-1'], $w->slot(4))->sole());

    $this->actingAs($admin)
        ->patch(route('admin.hospitals.status', $w->hospital), ['status' => 'inactive'])
        ->assertSessionHas('success', fn ($m) => str_contains($m, '3 open appointments were cancelled'));

    foreach ([...$pending, $approved] as $a) {
        expect($a->fresh()->status)->toBe(AppointmentStatus::Cancelled)
            ->and($a->fresh()->reason)->toBe('hospital deactivated');
    }

    expect($w->hospital->fresh()->status)->toBe(HospitalStatus::Inactive)
        ->and(BookingWorld::reservationsAreConsistent())->toBeTrue()
        ->and($w->batch('OPV')->fresh()->reserved)->toBe(0)
        ->and($w->parent->notifications()->get()->where('data.event', 'cancelled'))->toHaveCount(3);

    $log = ActivityLog::where('action', 'hospital.deactivated')->sole();
    expect($log->causer_id)->toBe($admin->id)
        ->and($log->properties['appointments_cancelled'])->toBe(3)
        ->and(ActivityLog::where('action', 'appointment.auto_cancelled')->count())->toBe(3);
});

test('a hospital with no open appointments is deactivated without cancelling anything', function () {
    $w = $this->world;

    $this->actingAs(User::factory()->admin()->create())
        ->patch(route('admin.hospitals.status', $w->hospital), ['status' => 'inactive'])
        ->assertSessionHas('success', "{$w->hospital->name} has been deactivated.");
});

// 3. Unconfirmed bookings past their start ------------------------------------

test('the hourly job cancels pending bookings whose time has started', function () {
    $w = $this->world;
    $pending = $w->book(['OPV-1'], $w->slot(1, '09:00'))->sole();
    $approved = $w->service->approve($w->book(['PCV-1'], $w->slot(1, '10:00'))->sole());
    $future = $w->book(['Rota-1'], $w->slot(2, '09:00'))->sole();

    $this->travelTo('2026-03-03 09:05:00');
    $this->artisan('appointments:cancel-unconfirmed')->expectsOutputToContain('Cancelled 1 unconfirmed appointment')->assertSuccessful();

    expect($pending->fresh()->status)->toBe(AppointmentStatus::Cancelled)
        ->and($pending->fresh()->reason)->toBe(AppointmentService::REASON_NOT_CONFIRMED)
        ->and($approved->fresh()->status)->toBe(AppointmentStatus::Approved)
        ->and($future->fresh()->status)->toBe(AppointmentStatus::Pending)
        ->and($w->batch('OPV')->fresh()->reserved)->toBe(0)
        ->and($w->parent->notifications()->where('type', AppointmentUpdated::class)->get()->pluck('data.event'))->toContain('cancelled')
        ->and(ActivityLog::where('action', 'appointment.auto_cancelled')->sole()->causer_id)->toBeNull();
});

test('both jobs are on the schedule', function () {
    $events = collect(app(Schedule::class)->events())->map(fn ($e) => [$e->command, $e->expression]);

    expect($events->first(fn ($e) => str_contains($e[0], 'appointments:cancel-unconfirmed'))[1])->toBe('0 * * * *')
        ->and($events->first(fn ($e) => str_contains($e[0], 'reminders:send'))[1])->toBe('0 8 * * *');
});
