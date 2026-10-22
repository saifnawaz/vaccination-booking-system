<?php

/*
 * Checks every text/background pair the views use against WCAG 2.1 AA.
 * Reads the colour tokens straight from resources/css/app.css, so re-run it after
 * changing the palette:  php scripts/check-contrast.php
 * Exits 1 if any pair fails.
 */

$css = file_get_contents(__DIR__.'/../resources/css/app.css');
preg_match_all('/--color-([a-z0-9-]+):\s*(#[0-9a-f]{6})/i', $css, $m);
$t = array_combine($m[1], array_map('strtolower', $m[2])) + ['white' => '#ffffff'];

// Tokens defined as var(--color-…) point at another token.
preg_match_all('/--color-([a-z0-9-]+):\s*var\(--color-([a-z0-9-]+)\)/i', $css, $refs, PREG_SET_ORDER);
foreach ($refs as [, $name, $target]) {
    $t[$name] ??= $t[$target] ?? null;
}

function lum(string $hex): float
{
    $c = array_map(fn ($i) => hexdec(substr($hex, $i, 2)) / 255, [1, 3, 5]);
    $c = array_map(fn ($v) => $v <= 0.03928 ? $v / 12.92 : (($v + 0.055) / 1.055) ** 2.4, $c);

    return 0.2126 * $c[0] + 0.7152 * $c[1] + 0.0722 * $c[2];
}

function ratio(string $a, string $b): float
{
    [$x, $y] = [lum($a), lum($b)];

    return (max($x, $y) + 0.05) / (min($x, $y) + 0.05);
}

/** White at $alpha over $bg, as used for header text (text-white/80). */
function whiteOver(string $bg, float $alpha): string
{
    $c = array_map(fn ($i) => hexdec(substr($bg, $i, 2)), [1, 3, 5]);

    return sprintf('#%02x%02x%02x', ...array_map(fn ($v) => (int) round($v + (255 - $v) * $alpha), $c));
}

$t['white-80-on-forest'] = whiteOver($t['forest'], 0.8);

// [foreground, background, minimum, what it is]
$pairs = [
    // Body text
    ['ink', 'canvas', 4.5, 'body text'], ['ink', 'card', 4.5, 'text on cards'], ['ink', 'surface-muted', 4.5, 'table headers'],
    ['ink-muted', 'canvas', 4.5, 'secondary text'], ['ink-muted', 'card', 4.5, 'secondary text on cards'], ['ink-muted', 'surface-muted', 4.5, 'secondary on chips'],
    ['ink-subtle', 'canvas', 4.5, 'subtle text'], ['ink-subtle', 'card', 4.5, 'subtle on cards'], ['ink-subtle', 'surface-muted', 4.5, 'subtle on chips'],
    // Links and primary
    ['primary-700', 'canvas', 4.5, 'links'], ['primary-700', 'card', 4.5, 'links on cards'], ['primary-700', 'primary-50', 4.5, 'links on tint'],
    ['primary-800', 'primary-50', 4.5, 'active nav / badge'], ['primary-800', 'primary-100', 4.5, 'avatar initials'],
    ['white', 'primary-700', 4.5, 'primary button'], ['white', 'primary-800', 4.5, 'primary button hover'],
    // Header (forest)
    ['white', 'forest', 4.5, 'header text'], ['white-80-on-forest', 'forest', 4.5, 'header nav (80% white)'], ['white', 'forest-soft', 4.5, 'header active item'],
    // Status and messages
    ['success-700', 'success-50', 4.5, 'completed badge'], ['success-700', 'card', 4.5, 'success text on card'],
    ['warning-800', 'warning-50', 4.5, 'due / warning badge'], ['warning-800', 'card', 4.5, 'warning text on card'], ['warning-800', 'warning-100', 4.5, 'warning icon chip'],
    ['accent-700', 'accent-50', 4.5, 'low-stock text'], ['accent-700', 'canvas', 4.5, 'mustard-dark text'], ['white', 'accent-700', 4.5, 'accent button'],
    ['forest', 'accent-400', 4.5, 'text on mustard fill'],
    ['danger-700', 'danger-50', 4.5, 'overdue badge / error'], ['danger-700', 'canvas', 4.5, 'error text'], ['danger-700', 'card', 4.5, 'error on card'],
    ['white', 'danger-600', 4.5, 'destructive button'], ['white', 'danger-700', 4.5, 'destructive hover'],
    ['ink-muted', 'surface-muted', 4.5, 'missed / neutral badge'],
    // Non-text UI (WCAG 1.4.11, 3:1)
    ['line-input', 'surface', 3.0, 'field border'], ['line-input', 'card', 3.0, 'field border on card'],
    ['primary-600', 'canvas', 3.0, 'focus ring'], ['primary-600', 'card', 3.0, 'focus ring on card'],
    ['chart', 'card', 3.0, 'chart bars'], ['primary-600', 'surface-muted', 3.0, 'icons on chips'],
];

$failed = 0;
foreach ($pairs as [$fg, $bg, $min, $what]) {
    if (! isset($t[$fg], $t[$bg])) {
        printf("?? %-20s on %-15s missing token\n", $fg, $bg);
        $failed++;

        continue;
    }
    $r = ratio($t[$fg], $t[$bg]);
    $ok = $r >= $min;
    $failed += $ok ? 0 : 1;
    printf("%s %-20s on %-15s %5.2f:1 (needs %.1f)  %s\n", $ok ? 'ok  ' : 'FAIL', $fg, $bg, $r, $min, $what);
}

echo $failed ? "\n{$failed} pair(s) fail.\n" : "\nAll ".count($pairs)." pairs pass WCAG AA.\n";
exit($failed ? 1 : 0);
