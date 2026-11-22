@use('App\Enums\DoseStatus')
@php
    $counts = $schedule->counts();
    $total = array_sum($counts);
@endphp

<x-app-layout :title="$child->first_name.'\'s schedule'">

    <x-page-header :title="$child->fullName()"
                   :description="$child->ageLabel().' old · born '.$child->date_of_birth->format('j M Y')">
        <x-slot:breadcrumb>
            <a href="{{ route('parent.children.index') }}" class="inline-flex items-center gap-1 font-medium text-primary-700 hover:underline">
                <x-heroicon-o-chevron-left class="size-4" aria-hidden="true" /> Children
            </a>
        </x-slot:breadcrumb>
        <x-slot:actions>
            <x-button :href="route('parent.children.edit', $child)" variant="secondary">
                <x-heroicon-o-pencil-square class="size-4" aria-hidden="true" /> Edit details
            </x-button>
        </x-slot:actions>
    </x-page-header>

    {{-- Progress and counts --}}
    <x-card class="mb-6">
        <div class="flex items-center justify-between gap-4">
            <h2 class="text-base font-semibold text-ink">Vaccination progress</h2>
            <p class="text-sm font-semibold text-ink">{{ $counts['completed'] }} of {{ $total }} doses given</p>
        </div>
        <div class="mt-3 h-2.5 overflow-hidden rounded-full bg-surface-muted" role="progressbar"
             aria-valuemin="0" aria-valuemax="{{ $total }}" aria-valuenow="{{ $counts['completed'] }}"
             aria-label="Doses given">
            <div class="h-full rounded-full bg-success-500" style="width: {{ $total ? round($counts['completed'] / $total * 100) : 0 }}%"></div>
        </div>
        <ul class="mt-4 flex flex-wrap gap-2" aria-label="Doses by status">
            @foreach ([DoseStatus::Overdue, DoseStatus::Due, DoseStatus::Upcoming, DoseStatus::Completed, DoseStatus::Missed] as $status)
                @if ($counts[$status->value] > 0)
                    <li><x-badge :tone="$status->tone()">{{ $counts[$status->value] }} {{ strtolower($status->label()) }}</x-badge></li>
                @endif
            @endforeach
        </ul>

        @if ($counts['overdue'] + $counts['due'] > 0)
            <x-alert :type="$counts['overdue'] > 0 ? 'error' : 'warning'" class="mt-4">
                {{ $child->first_name }} has {{ $counts['overdue'] + $counts['due'] }}
                {{ str('dose')->plural($counts['overdue'] + $counts['due']) }} to get now.
                Booking appointments will be available soon.
            </x-alert>
        @endif
    </x-card>

    {{-- Schedule grouped by age --}}
    <div class="space-y-8">
        @foreach ($schedule->byAge() as $age => $doses)
            <section aria-labelledby="age-{{ $loop->index }}">
                <h2 id="age-{{ $loop->index }}" class="mb-3 flex items-baseline gap-2 text-lg font-semibold text-ink">
                    {{ $age }}
                    <span class="text-sm font-normal text-ink-muted">
                        {{ $child->date_of_birth->addDays($doses->first()->schedule()->age_days)->format('j M Y') }}
                    </span>
                </h2>
                <ul class="space-y-3">
                    @foreach ($doses as $dose)
                        @include('parent.children.partials.dose-row', ['dose' => $dose])
                    @endforeach
                </ul>
            </section>
        @endforeach
    </div>

    <p class="mt-10 text-sm text-ink-muted">
        This schedule follows the national routine immunisation programme. Always check with your doctor or vaccination centre.
    </p>
</x-app-layout>
