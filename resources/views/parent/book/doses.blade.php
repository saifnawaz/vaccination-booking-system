<x-app-layout :title="'Book for '.$child->first_name">
    <x-page-header :title="'Book for '.$child->first_name" :description="$child->ageLabel().' old'">
        <x-slot:breadcrumb>
            <a href="{{ route('parent.children.show', $child) }}" class="inline-flex items-center gap-1 font-medium text-primary-700 hover:underline">
                <x-heroicon-o-chevron-left class="size-4" aria-hidden="true" /> {{ $child->first_name }}'s schedule
            </a>
        </x-slot:breadcrumb>
    </x-page-header>

    <x-booking-steps :current="1" />

    @if ($rows->isEmpty())
        <x-empty-state title="Nothing to book right now" icon="heroicon-o-check-circle">
            {{ $child->first_name }} has no vaccines that can be booked at the moment. Check the schedule to see what's coming next.
            <x-slot:actions>
                <x-button :href="route('parent.children.show', $child)" variant="secondary">View schedule</x-button>
            </x-slot:actions>
        </x-empty-state>
    @else
        <form method="GET" action="{{ route('parent.book.hospitals', $child) }}" data-no-loading class="space-y-6">
            <fieldset>
                <legend class="text-lg font-semibold text-ink">Which vaccines?</legend>
                <p class="mt-1 text-sm text-ink-muted">Vaccines due at the same age are usually given in one visit. Choose all you'd like to book together.</p>

                <ul class="mt-4 space-y-3">
                    @foreach ($rows as ['dose' => $dose, 'appointment' => $appointment])
                        <li>
                            @if ($appointment)
                                <div class="flex items-start gap-3 rounded-xl bg-surface-muted p-4 ring-1 ring-line">
                                    <x-heroicon-o-calendar-days class="mt-0.5 size-5 shrink-0 text-primary-700" aria-hidden="true" />
                                    <div>
                                        <p class="font-semibold text-ink">{{ $dose->label() }} <span class="font-normal text-ink-muted">· already booked</span></p>
                                        <p class="text-sm text-ink-muted">
                                            {{ $appointment->timeSlot->date->format('D j M') }}, {{ $appointment->timeSlot->timeRange() }} at {{ $appointment->hospital->name }}
                                        </p>
                                    </div>
                                </div>
                            @else
                                <label class="flex cursor-pointer items-start gap-3 rounded-xl bg-surface p-4 ring-1 ring-line has-checked:bg-primary-50 has-checked:ring-primary-300">
                                    <input type="checkbox" name="doses[]" value="{{ $dose->dose->id }}"
                                           @checked(in_array($dose->dose->id, $preselected, true))
                                           class="mt-1 size-5 shrink-0 rounded border-line-input text-primary-700 focus:ring-primary-600">
                                    <span class="min-w-0 flex-1">
                                        <span class="flex flex-wrap items-center gap-x-3 gap-y-1">
                                            <span class="font-semibold text-ink">{{ $dose->label() }}</span>
                                            <x-badge :tone="$dose->status->tone()">{{ $dose->status->label() }}</x-badge>
                                        </span>
                                        <span class="mt-0.5 block text-sm text-ink-muted">{{ $dose->schedule()->vaccine->name }}</span>
                                        <span class="mt-1 block text-sm text-ink">{{ $dose->summary() }}</span>
                                    </span>
                                </label>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </fieldset>

            <div class="sticky bottom-20 z-30 md:static">
                <x-button size="lg" class="w-full sm:w-auto">
                    Find a hospital <x-heroicon-o-chevron-right class="size-4" aria-hidden="true" />
                </x-button>
            </div>
        </form>
    @endif
</x-app-layout>
