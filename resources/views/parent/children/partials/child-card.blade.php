{{-- Summary card for one child. Needs $child and $schedule (App\Services\VaccinationSchedule). --}}
@php
    $counts = $schedule->counts();
    $next = $schedule->nextAction();
    $total = array_sum($counts);
@endphp

<a href="{{ route('parent.children.show', $child) }}"
   class="block rounded-2xl bg-surface p-5 shadow-sm ring-1 ring-line transition hover:ring-primary-300">
    <div class="flex items-start justify-between gap-3">
        <div class="flex min-w-0 items-center gap-3">
            <span class="flex size-11 shrink-0 items-center justify-center rounded-full bg-primary-100 text-lg font-bold text-primary-800" aria-hidden="true">
                {{ mb_strtoupper(mb_substr($child->first_name, 0, 1)) }}
            </span>
            <div class="min-w-0">
                <p class="truncate font-semibold text-ink">{{ $child->fullName() }}</p>
                <p class="text-sm text-ink-muted">{{ $child->ageLabel() }} old</p>
            </div>
        </div>
        <x-heroicon-o-chevron-right class="mt-3 size-5 shrink-0 text-ink-subtle" aria-hidden="true" />
    </div>

    @if ($next)
        <div class="mt-4 flex flex-wrap items-center gap-2">
            <x-badge :tone="$next->status->tone()">{{ $next->status->label() }}</x-badge>
            <span class="text-sm font-medium text-ink">{{ $next->label() }}</span>
        </div>
        <p class="mt-1 text-sm text-ink-muted">{{ $next->summary() }}</p>
    @else
        <div class="mt-4 flex items-center gap-2 text-sm font-medium text-success-700">
            <x-heroicon-o-check-circle class="size-5" aria-hidden="true" /> All scheduled doses are done
        </div>
    @endif

    <p class="mt-4 text-sm text-ink-muted">
        {{ $counts['completed'] }} of {{ $total }} doses given
        @if ($counts['overdue'] > 0)
            · <span class="font-semibold text-danger-700">{{ $counts['overdue'] }} overdue</span>
        @endif
    </p>
</a>
