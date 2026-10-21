<?php

use App\Models\Hospital;
use App\Models\User;
use Tests\Support\BookingWorld;

beforeEach(function () {
    $this->travelTo('2026-03-02 08:00:00');
    $this->world = BookingWorld::create(childAgeDays: 120);
    $w = $this->world;

    // One dose given here, one copied from the card.
    $a = $w->service->approve($w->book(['BCG'], $w->slot(1))->sole());
    $this->travelTo('2026-03-03 10:00:00');
    $w->service->complete($a);
    $w->service->recordHistoricalDose($w->dose('OPV-0'), $w->hospital, now()->subDays(118)->toImmutable(), 'CARD-77');
});

test('the parent sees a printable certificate with historical records marked', function () {
    $w = $this->world;

    $this->actingAs($w->parent)->get(route('parent.children.certificate', $w->child))
        ->assertOk()
        ->assertSee('Vaccination certificate')
        ->assertSee($w->child->fullName())
        ->assertSee('BCG')
        ->assertSee('BCG-A')                         // batch given here
        ->assertSee('OPV-0')
        ->assertSee('CARD-77')                       // free-text batch from the card
        ->assertSee('Recorded from previous records')
        ->assertSee('must be verified against the official EPI Pakistan schedule')
        ->assertSee('Print');
});

test('the certificate downloads as a PDF', function () {
    $w = $this->world;

    $response = $this->actingAs($w->parent)->get(route('parent.children.certificate.pdf', $w->child));

    $response->assertOk()->assertHeader('content-type', 'application/pdf');
    expect(substr($response->getContent(), 0, 4))->toBe('%PDF')
        ->and($response->headers->get('content-disposition'))->toContain('vaccination-certificate.pdf');
});

test("nobody else can see a child's certificate", function () {
    $w = $this->world;

    foreach ([User::factory()->parent()->create(), Hospital::factory()->active()->create()->user, User::factory()->admin()->create()] as $user) {
        $this->actingAs($user)->get(route('parent.children.certificate', $w->child))->assertForbidden();
        $this->actingAs($user)->get(route('parent.children.certificate.pdf', $w->child))->assertForbidden();
    }
});
