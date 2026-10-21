{{--
    Inline message box. type: success, error, warning, info.
    Errors use role="alert" so screen readers announce them immediately.
--}}
@props(['type' => 'info', 'title' => null, 'dismissible' => false])

@php
    $styles = [
        'success' => ['bg-success-50 text-success-700 ring-success-100', 'heroicon-o-check-circle'],
        'error' => ['bg-danger-50 text-danger-700 ring-danger-100', 'heroicon-o-x-circle'],
        'warning' => ['bg-warning-50 text-warning-800 ring-warning-100', 'heroicon-o-exclamation-triangle'],
        'info' => ['bg-primary-50 text-primary-800 ring-primary-100', 'heroicon-o-information-circle'],
    ];
    [$classes, $icon] = $styles[$type];
@endphp

<div
    @if ($dismissible) x-data="{ open: true }" x-show="open" x-transition.opacity @endif
    role="{{ $type === 'error' ? 'alert' : 'status' }}"
    {{ $attributes->merge(['class' => 'flex items-start gap-3 rounded-xl p-4 text-sm ring-1 ring-inset '.$classes]) }}
>
    <x-dynamic-component :component="$icon" class="size-5 shrink-0" aria-hidden="true" />

    <div class="min-w-0 flex-1">
        @if ($title)
            <p class="font-semibold">{{ $title }}</p>
        @endif
        <div @class(['mt-1' => $title])>{{ $slot }}</div>
    </div>

    @if ($dismissible)
        <button type="button" x-on:click="open = false" class="-m-1 rounded-md p-1 hover:bg-black/5">
            <x-heroicon-o-x-mark class="size-4" aria-hidden="true" />
            <span class="sr-only">Dismiss</span>
        </button>
    @endif
</div>
