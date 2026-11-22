<?php

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Process;
use Tests\Support\BookingWorld;

test('pages carry the Tika name, tagline and favicon', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('<title>Tika</title>', false)
        ->assertSee('Every dose, on time.')
        ->assertSee('favicon.svg');

    $this->get('/login')->assertOk()->assertSee('<title>Log in · Tika</title>', false);

    expect(file_exists(public_path('favicon.svg')))->toBeTrue();
});

test('the logo intro plays on log-in and registration only', function () {
    $this->get('/login')->assertSee('tika-intro', false)->assertSee('tika-intro-word', false);
    $this->get('/register')->assertSee('tika-intro', false);
    $this->get('/forgot-password')->assertDontSee('tika-intro', false);
});

test('the logo is three rounded bars rotated 0, 60 and 120 degrees', function () {
    $svg = Blade::render('<x-logo size="48" />');

    expect(substr_count($svg, '<rect'))->toBe(3)
        ->and($svg)->toContain('width="48"')
        ->toContain('rotate(0 50 50)')->toContain('rotate(60 50 50)')->toContain('rotate(120 50 50)')
        ->toContain('#2FB37A')->toContain('#1F8A5B')->toContain('#0F3D2E')
        ->toContain('mix-blend-mode: multiply')
        ->toContain('isolation: isolate');
});

test('status badges always show an icon next to the label', function () {
    $this->travelTo('2026-03-02 08:00:00');
    $w = BookingWorld::create(childAgeDays: 84); // overdue and missed doses

    $html = $this->actingAs($w->parent)->get(route('parent.children.show', $w->child))->assertOk()->getContent();

    // Each status pill contains an <svg> icon before its text.
    preg_match_all('/<span class="inline-flex items-center gap-1\.5 rounded-full[^"]*">\s*<svg/', $html, $withIcon);
    expect(count($withIcon[0]))->toBeGreaterThanOrEqual(19); // one per dose
    expect($html)->toContain('Overdue')->toContain('Missed');
});

test('every colour pair in the palette passes WCAG AA', function () {
    $result = Process::path(base_path())->run([PHP_BINARY, 'scripts/check-contrast.php']);

    expect($result->exitCode())->toBe(0, $result->output())
        ->and($result->output())->toContain('pairs pass WCAG AA');
});

test('motion is switched off for people who prefer reduced motion', function () {
    $css = file_get_contents(resource_path('css/app.css'));

    expect($css)->toContain('@media (prefers-reduced-motion: reduce)')
        ->toContain('animation-duration: 0.01ms !important');
});
