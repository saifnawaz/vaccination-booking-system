<x-app-layout title="Time slots">
    <x-page-header title="Time slots" description="When parents can book a vaccination with you.">
        <x-slot:actions>
            <x-button :href="route('hospital.slots.create')" variant="secondary">
                <x-heroicon-o-plus class="size-4" aria-hidden="true" /> Add one slot
            </x-button>
            <x-button :href="route('hospital.slots.bulk')">
                <x-heroicon-o-squares-plus class="size-4" aria-hidden="true" /> Create in bulk
            </x-button>
        </x-slot:actions>
    </x-page-header>

    <form method="GET" action="{{ route('hospital.slots.index') }}" data-no-loading
          class="mb-6 flex flex-col gap-3 rounded-2xl bg-surface p-4 ring-1 ring-line sm:flex-row sm:items-end">
        <div>
            <x-input-label for="from" value="From" />
            <x-text-input id="from" name="from" type="date" :value="$filters['from']" class="mt-1.5" />
        </div>
        <div>
            <x-input-label for="to" value="To" />
            <x-text-input id="to" name="to" type="date" :value="$filters['to']" class="mt-1.5" />
        </div>
        <div class="flex gap-2">
            <x-button variant="secondary">Show</x-button>
            <x-button :href="route('hospital.slots.index')" variant="ghost">From today</x-button>
        </div>
    </form>

    @if ($slots->isEmpty())
        <x-empty-state title="No slots in this period" icon="heroicon-o-calendar-days">
            Create your opening hours in one go, for example every weekday morning for the next month.
            <x-slot:actions>
                <x-button :href="route('hospital.slots.bulk')">Create slots in bulk</x-button>
            </x-slot:actions>
        </x-empty-state>
    @else
        <div class="space-y-6">
            @foreach ($byDate as $date => $daySlots)
                @php($day = \Carbon\CarbonImmutable::parse($date))
                <section aria-labelledby="day-{{ $date }}">
                    <h2 id="day-{{ $date }}" class="mb-3 text-base font-semibold text-ink">
                        {{ $day->format('l j F Y') }}
                        @if ($day->isToday()) <x-badge tone="primary" class="ms-2">Today</x-badge> @endif
                    </h2>
                    <ul class="grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($daySlots as $slot)
                            <li class="flex items-center justify-between gap-3 rounded-xl bg-surface px-4 py-3 ring-1 ring-line">
                                <div>
                                    <p class="font-medium text-ink">{{ $slot->timeRange() }}</p>
                                    <p class="text-sm text-ink-muted">{{ $slot->capacity }} {{ str('child')->plural($slot->capacity) }} per slot</p>
                                </div>
                                <div class="flex gap-1">
                                    <x-button :href="route('hospital.slots.edit', $slot)" variant="ghost" size="sm">
                                        <x-heroicon-o-pencil-square class="size-4" aria-hidden="true" />
                                        <span class="sr-only">Change capacity for {{ $day->format('j M') }}, {{ $slot->timeRange() }}</span>
                                    </x-button>
                                    <x-button type="button" variant="ghost" size="sm" class="text-danger-700 hover:bg-danger-50"
                                              x-data x-on:click="$dispatch('open-modal', 'remove-slot-{{ $slot->id }}')">
                                        <x-heroicon-o-trash class="size-4" aria-hidden="true" />
                                        <span class="sr-only">Remove {{ $day->format('j M') }}, {{ $slot->timeRange() }}</span>
                                    </x-button>
                                </div>
                                <x-confirm-dialog :name="'remove-slot-'.$slot->id" title="Remove this slot?"
                                                  :action="route('hospital.slots.destroy', $slot)" method="DELETE" confirm-label="Remove slot">
                                    {{ $day->format('l j F') }}, {{ $slot->timeRange() }} will no longer be offered to parents.
                                </x-confirm-dialog>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endforeach
        </div>

        <div class="mt-6">{{ $slots->links() }}</div>
    @endif
</x-app-layout>
