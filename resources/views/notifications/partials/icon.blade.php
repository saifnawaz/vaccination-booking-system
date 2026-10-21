@php
    [$classes, $icon] = match ($kind) {
        'success' => ['bg-success-50 text-success-700', 'heroicon-o-check-circle'],
        'danger' => ['bg-danger-50 text-danger-700', 'heroicon-o-exclamation-circle'],
        'warning' => ['bg-warning-50 text-warning-800', 'heroicon-o-bell'],
        default => ['bg-primary-50 text-primary-700', 'heroicon-o-information-circle'],
    };
@endphp
<span class="flex size-9 shrink-0 items-center justify-center rounded-full {{ $classes }}" aria-hidden="true">
    <x-dynamic-component :component="$icon" class="size-5" />
</span>
