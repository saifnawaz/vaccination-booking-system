{{--
    The Tika mark: an asterisk of three rounded bars rotated 0°, 60° and 120° around
    the centre, multiplied together so the overlaps darken.
    <x-logo />                     24px mark
    <x-logo size="48" />           any pixel size
    <x-logo animated />            login/register intro: bars rotate into place one after another
    <x-logo spinning />            slow rotation, used as the loading spinner
    `isolation: isolate` keeps the multiply blend between the bars only, so the
    mark reads the same on light pages and on the forest header.
--}}
@props(['size' => 24, 'animated' => false, 'spinning' => false, 'label' => null])

@php
    // [fill, angle, intro delay]. The angle is set twice: as an SVG transform attribute, so the
    // mark is right anywhere (certificate, PDF, email, no CSS), and as --r for the CSS animation.
    $bars = [
        ['#2FB37A', 0, '0ms'],
        ['#1F8A5B', 60, '120ms'],
        ['#0F3D2E', 120, '240ms'],
    ];
@endphp

<svg viewBox="0 0 100 100" width="{{ $size }}" height="{{ $size }}"
     @if ($label) role="img" aria-label="{{ $label }}" @else aria-hidden="true" focusable="false" @endif
     {{ $attributes->class(['shrink-0', 'tika-intro' => $animated, 'tika-spin' => $spinning]) }}
     style="isolation: isolate">
    @foreach ($bars as [$fill, $rotate, $delay])
        <rect class="tika-bar" x="39" y="6" width="22" height="88" rx="10"
              fill="{{ $fill }}" opacity="0.9" transform="rotate({{ $rotate }} 50 50)"
              style="--r: {{ $rotate }}deg; --d: {{ $delay }}; mix-blend-mode: multiply" />
    @endforeach
</svg>
