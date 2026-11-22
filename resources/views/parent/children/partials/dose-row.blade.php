{{-- One scheduled dose: $dose is an App\Services\ScheduledDose. --}}
@use('App\Enums\DoseStatus')
@php
    $status = $dose->status;
    [$iconClasses, $icon] = match ($status) {
        DoseStatus::Completed => ['bg-success-50 text-success-700', 'heroicon-o-check-circle'],
        DoseStatus::Due => ['bg-warning-100 text-warning-800', 'heroicon-o-bell'],
        DoseStatus::Overdue => ['bg-danger-100 text-danger-700', 'heroicon-o-exclamation-circle'],
        DoseStatus::Upcoming => ['bg-warning-50 text-warning-800', 'heroicon-o-clock'],
        DoseStatus::Missed => ['bg-surface-muted text-ink-subtle', 'heroicon-o-minus-circle'],
    };
@endphp

<li @class([
    'flex items-start gap-3 rounded-xl p-4 ring-1 ring-inset',
    'bg-card ring-line' => ! $status->needsAction(),
    'bg-accent-50 ring-accent-200' => $status === DoseStatus::Due,
    'bg-danger-50 ring-danger-100' => $status === DoseStatus::Overdue,
])>
    <span class="mt-0.5 flex size-8 shrink-0 items-center justify-center rounded-full {{ $iconClasses }}" aria-hidden="true">
        <x-dynamic-component :component="$icon" class="size-5" />
    </span>

    <div class="min-w-0 flex-1">
        <div class="flex flex-wrap items-center gap-x-3 gap-y-1">
            <p class="font-semibold text-ink">{{ $dose->label() }}</p>
            <x-status :status="$status" />
        </div>
        <p class="mt-0.5 text-sm text-ink-muted">{{ $dose->schedule()->vaccine->name }}</p>
        <p class="mt-1 text-sm text-ink">{{ $dose->summary() }}</p>
    </div>
</li>
