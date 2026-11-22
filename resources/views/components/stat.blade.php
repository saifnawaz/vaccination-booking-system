{{--
    A dashboard number. Values must come from the database: never pass a literal.
    tone highlights numbers that need attention (e.g. pending approvals > 0).
--}}
@props(['label', 'value', 'icon', 'href' => null, 'tone' => 'primary'])

@php
    $iconTones = [
        'primary' => 'bg-primary-50 text-primary-700',
        'accent' => 'bg-accent-50 text-accent-700',
        'success' => 'bg-success-50 text-success-700',
        'neutral' => 'bg-surface-muted text-ink-muted',
    ];
    $tag = $href ? 'a' : 'div';
@endphp

<{{ $tag }}
    @if ($href) href="{{ $href }}" @endif
    {{ $attributes->merge(['class' => 'flex items-center gap-4 rounded-2xl bg-surface p-5 shadow-sm ring-1 ring-line'.($href ? ' transition hover:ring-primary-300' : '')]) }}
>
    <span class="flex size-11 shrink-0 items-center justify-center rounded-xl {{ $iconTones[$tone] }}">
        <x-dynamic-component :component="$icon" class="size-6" aria-hidden="true" />
    </span>
    <span class="min-w-0">
        <span class="block text-sm font-medium text-ink-muted">{{ $label }}</span>
        <span class="block text-2xl font-bold tabular-nums text-ink">{{ number_format($value) }}</span>
    </span>
</{{ $tag }}>
