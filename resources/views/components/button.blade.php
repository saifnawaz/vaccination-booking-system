{{--
    Buttons and button-styled links.
    <x-button>Save</x-button>                       primary submit button
    <x-button variant="secondary" type="button">    other variants: danger, accent, ghost
    <x-button :href="route('…')">                   renders an <a>
    Inside a submitted form the spinner shows automatically (see resources/js/app.js).
--}}
@props([
    'variant' => 'primary',
    'size' => 'md',
    'href' => null,
    'type' => 'submit',
])

@php
    $base = 'group inline-flex items-center justify-center gap-2 rounded-lg font-semibold transition-colors '
        .'focus-visible:outline-2 focus-visible:outline-offset-2 '
        .'disabled:cursor-not-allowed disabled:opacity-60';

    $sizes = [
        'sm' => 'px-3 py-1.5 text-sm',
        'md' => 'px-4 py-2.5 text-sm',
        'lg' => 'px-5 py-3 text-base',
    ];

    $variants = [
        'primary' => 'bg-primary-700 text-white shadow-sm hover:bg-primary-800 focus-visible:outline-primary-500',
        'secondary' => 'bg-surface text-ink shadow-sm ring-1 ring-inset ring-line-input hover:bg-surface-muted focus-visible:outline-primary-500',
        'danger' => 'bg-danger-600 text-white shadow-sm hover:bg-danger-700 focus-visible:outline-danger-500',
        'accent' => 'bg-accent-700 text-white shadow-sm hover:bg-accent-800 focus-visible:outline-accent-500',
        'ghost' => 'text-primary-700 hover:bg-primary-50 focus-visible:outline-primary-500',
    ];

    $classes = $base.' '.$sizes[$size].' '.$variants[$variant];
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
        <x-heroicon-o-arrow-path class="hidden size-4 shrink-0 animate-spin group-data-loading:block" aria-hidden="true" />
        {{ $slot }}
    </button>
@endif
