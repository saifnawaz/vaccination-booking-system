{{-- Summary card for one child. Needs $child and $schedule (App\Services\VaccinationSchedule). --}}
@php
    $counts = $schedule->counts();
    $next = $schedule->nextAction();
    $total = array_sum($counts);
    $done = $counts['completed'];
@endphp

<a href="{{ route('parent.children.show', $child) }}"
   class="group block animate-fade-up rounded-2xl bg-card p-5 shadow-sm ring-1 ring-line transition duration-200 ease-out hover:-translate-y-0.5 hover:shadow-md hover:ring-primary-300">
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
        <x-heroicon-o-chevron-right class="mt-3 size-5 shrink-0 text-ink-subtle transition-transform duration-200 group-hover:translate-x-0.5" aria-hidden="true" />
    </div>

    {{-- Progress: the text carries the number; the bar only illustrates it. --}}
    <div class="mt-4">
        <div class="flex items-baseline justify-between text-sm">
            <span class="font-semibold text-ink">{{ $done }}/{{ $total }} doses done</span>
            @if ($counts['overdue'] > 0)
                <span class="inline-flex items-center gap-1 font-semibold text-danger-700">
                    <x-heroicon-o-exclamation-circle class="size-4" aria-hidden="true" /> {{ $counts['overdue'] }} overdue
                </span>
            @endif
        </div>
        <div class="mt-1.5 h-2 overflow-hidden rounded-full bg-surface-muted" role="progressbar"
             aria-valuemin="0" aria-valuemax="{{ $total }}" aria-valuenow="{{ $done }}" aria-label="{{ $child->first_name }}: {{ $done }} of {{ $total }} doses done">
            <div class="h-full origin-left animate-[grow-x_250ms_var(--ease-out-soft)_both] rounded-full bg-primary-500" style="width: {{ $total ? round($done / $total * 100) : 0 }}%"></div>
        </div>
    </div>

    @if ($next)
        <div class="mt-4 flex flex-wrap items-center gap-2">
            <x-status :status="$next->status" />
            <span class="text-sm font-medium text-ink">{{ $next->label() }}</span>
        </div>
        <p class="mt-1 text-sm text-ink-muted">{{ $next->summary() }}</p>
    @else
        <div class="mt-4 flex items-center gap-2 text-sm font-medium text-success-700">
            <x-heroicon-o-check-circle class="size-5" aria-hidden="true" /> All scheduled doses are done
        </div>
    @endif
</a>
