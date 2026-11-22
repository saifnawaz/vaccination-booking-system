{{--
    Status pill. Tones map to the theme's status colours:
    success (completed/active), warning (due/pending), danger (overdue), accent, primary, neutral.
--}}
@props(['tone' => 'neutral'])

@php
    $tones = [
        'neutral' => 'bg-surface-muted text-ink-muted ring-line-strong',
        'primary' => 'bg-primary-50 text-primary-800 ring-primary-200',
        'success' => 'bg-success-50 text-success-700 ring-success-100',
        'warning' => 'bg-warning-50 text-warning-800 ring-warning-100',
        'danger' => 'bg-danger-50 text-danger-700 ring-danger-100',
        'accent' => 'bg-accent-50 text-accent-700 ring-accent-200',
    ];
    $dots = [
        'neutral' => 'bg-ink-subtle',
        'primary' => 'bg-primary-500',
        'success' => 'bg-success-500',
        'warning' => 'bg-warning-500',
        'danger' => 'bg-danger-500',
        'accent' => 'bg-accent-500',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1 ring-inset '.$tones[$tone]]) }}>
    <span class="size-1.5 rounded-full {{ $dots[$tone] }}" aria-hidden="true"></span>
    {{ $slot }}
</span>
