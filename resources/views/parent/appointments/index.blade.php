<x-app-layout title="Appointments">
    <x-page-header title="Appointments" description="Upcoming visits and your booking history.">
        <x-slot:actions>
            <x-button :href="route('parent.book')">
                <x-heroicon-o-plus class="size-4" aria-hidden="true" /> Book a vaccination
            </x-button>
        </x-slot:actions>
    </x-page-header>

    <section aria-labelledby="upcoming-heading">
        <h2 id="upcoming-heading" class="mb-3 text-lg font-semibold text-ink">Upcoming</h2>

        @if ($visits->isEmpty())
            <x-empty-state title="No upcoming appointments" icon="heroicon-o-calendar-days">
                When you book a vaccination it will appear here.
                <x-slot:actions>
                    <x-button :href="route('parent.book')">Book a vaccination</x-button>
                </x-slot:actions>
            </x-empty-state>
        @else
            <ul class="space-y-4">
                @foreach ($visits as $visit)
                    @php($first = $visit->first())
                    <li>
                        <x-card>
                            <div class="flex flex-col gap-1 sm:flex-row sm:items-start sm:justify-between">
                                <div>
                                    <p class="text-lg font-semibold text-ink">{{ $first->timeSlot->date->format('l j F') }}, {{ $first->timeSlot->timeRange() }}</p>
                                    <p class="text-ink-muted">{{ $first->child->fullName() }} · {{ $first->hospital->name }}, {{ $first->hospital->city }}</p>
                                </div>
                            </div>

                            <ul class="mt-4 divide-y divide-line rounded-xl ring-1 ring-line">
                                @foreach ($visit as $appointment)
                                    <li class="flex flex-col gap-3 p-4 sm:flex-row sm:items-center sm:justify-between">
                                        <div class="flex flex-wrap items-center gap-x-3 gap-y-1">
                                            <span class="font-semibold text-ink">{{ $appointment->doseLabel() }}</span>
                                            <x-badge :tone="$appointment->status->tone()">{{ $appointment->status->label() }}</x-badge>
                                        </div>

                                        @if ($appointment->canBeChangedByParent())
                                            <div class="flex gap-2">
                                                <x-button :href="route('parent.appointments.reschedule', $appointment)" variant="secondary" size="sm">
                                                    Change time <span class="sr-only">for {{ $appointment->doseLabel() }}</span>
                                                </x-button>
                                                <x-button type="button" variant="ghost" size="sm" class="text-danger-700 hover:bg-danger-50"
                                                          x-data x-on:click="$dispatch('open-modal', 'cancel-{{ $appointment->id }}')">
                                                    Cancel <span class="sr-only">{{ $appointment->doseLabel() }}</span>
                                                </x-button>
                                            </div>
                                            <x-confirm-dialog :name="'cancel-'.$appointment->id" :title="'Cancel '.$appointment->doseLabel().'?'"
                                                              :action="route('parent.appointments.cancel', $appointment)" method="PATCH" confirm-label="Cancel appointment">
                                                {{ $appointment->child->first_name }}'s {{ $appointment->doseLabel() }} on {{ $appointment->timeSlot->date->format('j F') }} will be cancelled
                                                and the place given to someone else. You can book again at any time.
                                            </x-confirm-dialog>
                                        @else
                                            <p class="text-sm text-ink-muted">Within {{ config('vaccination.booking.change_cutoff_hours') }} hours: contact the hospital to change it.</p>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        </x-card>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>

    <section aria-labelledby="past-heading" class="mt-10">
        <h2 id="past-heading" class="mb-3 text-lg font-semibold text-ink">History</h2>

        <form method="GET" action="{{ route('parent.appointments.index') }}" data-no-loading class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-end">
            @if ($children->count() > 1)
                <div class="sm:w-52">
                    <x-input-label for="child" value="Child" />
                    <select id="child" name="child" class="mt-1.5 block w-full rounded-lg border-line-input bg-surface text-ink shadow-xs focus:border-primary-600 focus:ring-primary-600">
                        <option value="">All children</option>
                        @foreach ($children as $c)
                            <option value="{{ $c->id }}" @selected((int) ($filters['child'] ?? 0) === $c->id)>{{ $c->first_name }} {{ $c->last_name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
            <div class="sm:w-52">
                <x-input-label for="history-status" value="Status" />
                <select id="history-status" name="status" class="mt-1.5 block w-full rounded-lg border-line-input bg-surface text-ink shadow-xs focus:border-primary-600 focus:ring-primary-600">
                    <option value="">Any</option>
                    @foreach (['completed', 'cancelled', 'rejected', 'no_show'] as $s)
                        <option value="{{ $s }}" @selected(($filters['status'] ?? null) === $s)>{{ \App\Enums\AppointmentStatus::from($s)->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-2">
                <x-button variant="secondary">Filter</x-button>
                @if (array_filter($filters))
                    <x-button :href="route('parent.appointments.index')" variant="ghost">Clear</x-button>
                @endif
            </div>
        </form>

        @if ($past->isEmpty())
            <p class="text-sm text-ink-muted">
                {{ array_filter($filters) ? 'No past appointments match.' : 'Past, cancelled and declined appointments will appear here.' }}
            </p>
        @else
            <x-card :padding="false" class="overflow-hidden">
                <ul class="divide-y divide-line">
                    @foreach ($past as $appointment)
                        <li class="flex flex-col gap-1 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <p class="font-semibold text-ink">{{ $appointment->doseLabel() }} · {{ $appointment->child->first_name }}</p>
                                <p class="text-sm text-ink-muted">{{ $appointment->timeSlot->date->format('j M Y') }} · {{ $appointment->hospital->name }}</p>
                                @if ($appointment->reason)
                                    <p class="mt-1 text-sm text-ink">Reason: {{ $appointment->reason }}</p>
                                @endif
                            </div>
                            <x-badge :tone="$appointment->status->tone()">{{ $appointment->status->label() }}</x-badge>
                        </li>
                    @endforeach
                </ul>
            </x-card>
            <div class="mt-6">{{ $past->links() }}</div>
        @endif
    </section>
</x-app-layout>
