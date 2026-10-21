<?php

use App\Models\User;
use App\Notifications\AppointmentUpdated;
use App\Notifications\DosesDueSoon;
use App\Notifications\DosesOverdue;
use App\Services\DoseReminders;
use Illuminate\Support\Str;
use Tests\Support\BookingWorld;

beforeEach(function () {
    $this->travelTo('2026-03-02 08:00:00');
    $this->world = BookingWorld::create(childAgeDays: 49);
});

test('parents are told when a booking is confirmed, declined or missed', function () {
    $w = $this->world;
    [$a, $b, $c] = [
        $w->book(['OPV-1'], $w->slot(3, '09:00'))->sole(),
        $w->book(['PCV-1'], $w->slot(3, '10:00'))->sole(),
        $w->book(['Rota-1'], $w->slot(1, '11:00'))->sole(),
    ];

    $w->service->approve($a);
    $w->service->reject($b, 'Fully booked that day');
    $w->service->approve($c);
    $this->travelTo('2026-03-03 12:00:00');
    $w->service->markNoShow($c);

    $events = $w->parent->notifications()->where('type', AppointmentUpdated::class)->get()->pluck('data.event')->all();
    expect($events)->toEqualCanonicalizing(['approved', 'rejected', 'approved', 'no_show']);

    $declined = $w->parent->notifications()->get()->first(fn ($n) => $n->data['event'] === 'rejected');
    expect($declined->data['body'])->toContain('Fully booked that day');
});

test('a parent is not notified about their own cancellation', function () {
    $w = $this->world;
    $w->service->cancel($w->book(['OPV-1'], $w->slot())->sole());

    expect($w->parent->notifications()->count())->toBe(0);
});

test('the bell shows the unread count and the list marks things read', function () {
    $w = $this->world;
    $w->service->approve($w->book(['OPV-1'], $w->slot())->sole());
    $notification = $w->parent->notifications()->sole();

    $this->actingAs($w->parent)->get(route('parent.dashboard'))->assertOk()->assertSee('Notifications, 1 unread');
    $this->get(route('notifications.index'))->assertOk()->assertSee('Appointment confirmed');

    $this->get(route('notifications.open', $notification->id))->assertRedirect(route('parent.appointments.index', absolute: false));
    expect($notification->fresh()->read_at)->not->toBeNull();

    $this->get(route('notifications.index', ['show' => 'unread']))->assertSee('No unread notifications');
});

test('mark all as read', function () {
    $w = $this->world;
    $w->service->approve($w->book(['OPV-1'], $w->slot(3, '09:00'))->sole());
    $w->service->approve($w->book(['PCV-1'], $w->slot(3, '10:00'))->sole());

    $this->actingAs($w->parent)->post(route('notifications.read-all'))->assertRedirect();

    expect($w->parent->unreadNotifications()->count())->toBe(0);
});

test("nobody can open or mark someone else's notification", function () {
    $w = $this->world;
    $w->service->approve($w->book(['OPV-1'], $w->slot())->sole());
    $id = $w->parent->notifications()->sole()->id;
    $stranger = User::factory()->parent()->create();

    $this->actingAs($stranger)->get(route('notifications.open', $id))->assertNotFound();
    $this->actingAs($stranger)->post(route('notifications.read', $id))->assertNotFound();

    expect($w->parent->notifications()->sole()->read_at)->toBeNull();
});

test('opening a notification only ever redirects within the app', function () {
    $w = $this->world;
    $w->parent->notifications()->create([
        'id' => (string) Str::uuid(),
        'type' => AppointmentUpdated::class,
        'data' => ['title' => 'x', 'body' => 'x', 'url' => 'https://evil.example.com'],
    ]);

    $this->actingAs($w->parent)->get(route('notifications.open', $w->parent->notifications()->sole()->id))
        ->assertRedirect(route('notifications.index'));
});

test('reminders cover doses due within 7 days, once per dose', function () {
    // 5 weeks old: the 6-week doses become due in 7 days.
    $w = BookingWorld::create(childAgeDays: 35);
    $birth = $w->child->doses()->whereHas('scheduleDose', fn ($q) => $q->where('age_days', 0))->get();
    $birth->each(fn ($d) => tap($d)->forceFill(['administered_at' => now()->subDays(34)])->save());

    (new DoseReminders)->send();

    // (beforeEach's other world has its own parent; only this parent is checked.)
    $notification = $w->parent->notifications()->where('type', DosesDueSoon::class)->sole();
    expect($notification->data['child_dose_ids'])->toHaveCount(4)
        ->and($notification->data['body'])->toContain('OPV-1')
        ->and($w->parent->notifications()->where('type', DosesOverdue::class)->count())->toBe(0);

    (new DoseReminders)->send();
    expect($w->parent->notifications()->count())->toBe(1); // not sent again
});

test('overdue reminders are sent, and booked doses are left out', function () {
    $w = BookingWorld::create(childAgeDays: 84); // 6-week doses overdue, birth doses missed or overdue
    $w->book(['OPV-1'], $w->slot());

    (new DoseReminders)->send();

    $overdue = $w->parent->notifications()->where('type', DosesOverdue::class)->sole();
    $labels = $w->child->doses()->whereIn('id', $overdue->data['child_dose_ids'])->with('scheduleDose')->get()->pluck('scheduleDose.label');
    expect($labels)->toContain('Pentavalent-1')->not->toContain('OPV-1');
});
