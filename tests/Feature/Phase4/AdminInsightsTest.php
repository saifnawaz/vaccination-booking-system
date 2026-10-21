<?php

use App\Models\ActivityLog;
use App\Models\Child;
use App\Models\Hospital;
use App\Models\HospitalStock;
use App\Models\User;
use App\Models\Vaccine;
use App\Services\AdminStats;
use Tests\Support\BookingWorld;

beforeEach(function () {
    $this->travelTo('2026-03-02 08:00:00');
    $this->admin = User::factory()->admin()->create();
});

test('dashboard stats are counted from the database', function () {
    $w = BookingWorld::create(childAgeDays: 49);       // 6-week doses due, none overdue
    $w->childAged(84);                                 // 6-week doses overdue
    Hospital::factory()->count(2)->create();           // pending approval
    HospitalStock::factory()->expired()->create(['hospital_id' => $w->hospital->id, 'vaccine_id' => Vaccine::first()->id, 'quantity' => 5]);

    $a = $w->service->approve($w->book(['OPV-1'], $w->slot(1))->sole());
    $this->travelTo('2026-03-03 10:00:00');
    $w->service->complete($a);

    $stats = new AdminStats;
    expect($stats->childrenRegistered())->toBe(Child::count())
        ->and($stats->dosesGivenThisMonth())->toBe(1)
        ->and($stats->pendingApprovals())->toBe(2)
        ->and($stats->stockWarnings()['expired'])->toBe(1)
        ->and(collect($stats->dosesGivenByMonth())->last())->toBe(['label' => 'Mar 2026', 'value' => 1])
        ->and(collect($stats->dosesGivenByMonth())->sum('value'))->toBe(1);

    // The 12-week-old's four 6-week doses, plus the birth doses that can still be given late.
    expect($stats->overdueDoses())->toBe(collect($stats->overdueByVaccine())->sum('value'))
        ->and(collect($stats->overdueByVaccine())->pluck('label'))->toContain('Pentavalent (DTP-HepB-Hib)');

    $this->actingAs($this->admin)->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('Doses given per month')
        ->assertSee('Show as table')
        ->assertViewHas('dosesThisMonth', 1);
});

test('charts show an empty state rather than zero bars', function () {
    $this->actingAs($this->admin)->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('No doses have been given through the app yet.')
        ->assertSee('No overdue doses.');
});

test('the activity log records who approved a hospital and can be searched and filtered', function () {
    $hospital = Hospital::factory()->create(['name' => 'Searchable Clinic']);
    $vaccine = Vaccine::factory()->create(['name' => 'Logged vaccine']);

    $this->actingAs($this->admin)->patch(route('admin.hospitals.status', $hospital), ['status' => 'active']);
    $this->actingAs($this->admin)->delete(route('admin.vaccines.destroy', $vaccine));

    $entry = ActivityLog::where('action', 'hospital.approved')->sole();
    expect($entry->causer_id)->toBe($this->admin->id)
        ->and($entry->subject->is($hospital))->toBeTrue()
        ->and(ActivityLog::where('action', 'vaccine.deleted')->exists())->toBeTrue();

    $this->get(route('admin.activity.index'))->assertOk()->assertSee('Approved Searchable Clinic')->assertSee($this->admin->name);
    $this->get(route('admin.activity.index', ['search' => 'Searchable']))->assertSee('Searchable Clinic')->assertDontSee('Logged vaccine');
    $this->get(route('admin.activity.index', ['area' => 'vaccine']))->assertSee('Logged vaccine')->assertDontSee('Searchable Clinic');
    $this->get(route('admin.activity.index', ['from' => '2026-04-01']))->assertSee('Nothing matches');
});

test('hospital and parent actions are logged too', function () {
    $w = BookingWorld::create(childAgeDays: 49);
    $a = $w->book(['OPV-1'], $w->slot())->sole();

    $this->actingAs($w->hospital->user)->patch(route('hospital.appointments.approve', $a));
    $this->actingAs($w->parent)->patch(route('parent.appointments.cancel', $a));

    expect(ActivityLog::where('action', 'appointment.approved')->sole()->causer_id)->toBe($w->hospital->user->id)
        ->and(ActivityLog::where('action', 'appointment.cancelled')->sole()->causer_id)->toBe($w->parent->id);
});

test('only admins see the activity log', function () {
    $w = BookingWorld::create();

    $this->actingAs($w->parent)->get(route('admin.activity.index'))->assertForbidden();
    $this->actingAs($w->hospital->user)->get(route('admin.activity.index'))->assertForbidden();
});

test('the children list and appointment history can be searched, filtered and paginated', function () {
    $w = BookingWorld::create();
    foreach (range(1, 13) as $i) {
        $w->childAged(30 + $i)->update(['first_name' => "Kid{$i}"]);
    }

    $this->actingAs($w->parent);
    $this->get(route('parent.children.index'))->assertOk()->assertSee('Showing');
    $this->get(route('parent.children.index', ['search' => 'Kid13']))->assertSee('Kid13')->assertDontSee('Kid12');

    $done = $w->book(['OPV-1'], $w->slot(3, '09:00'))->sole();
    $w->service->cancel($done);
    $this->get(route('parent.appointments.index', ['status' => 'cancelled']))->assertSee('OPV-1');
    $this->get(route('parent.appointments.index', ['status' => 'completed']))->assertSee('No past appointments match.');
});
