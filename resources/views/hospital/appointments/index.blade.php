@use('App\Enums\AppointmentStatus')

<x-app-layout title="Appointments">
    <x-page-header title="Appointments"
                   :description="$pendingCount > 0 ? $pendingCount.' '.str('booking')->plural($pendingCount).' waiting for you to confirm.' : 'Bookings parents have made with you.'" />

    <form method="GET" action="{{ route('hospital.appointments.index') }}" data-no-loading data-filter
          class="mb-6 flex flex-col gap-3 rounded-2xl bg-card p-4 ring-1 ring-line sm:flex-row sm:items-end">
        <div class="flex-1">
            <x-input-label for="search" value="Child's name" />
            <x-text-input id="search" name="search" type="search" :value="$filters['search'] ?? ''" class="mt-1.5" />
        </div>
        <div>
            <x-input-label for="date" value="Date" />
            <x-text-input id="date" name="date" type="date" :value="$filters['date'] ?? ''" class="mt-1.5" />
        </div>
        <div class="sm:w-52">
            <x-input-label for="status" value="Show" />
            <select id="status" name="status" class="mt-1.5 block w-full rounded-lg border-line-input bg-surface text-ink shadow-xs focus:border-primary-600 focus:ring-primary-600">
                <option value="open" @selected($filters['status'] === 'open')>Open (to confirm or give)</option>
                @foreach (AppointmentStatus::cases() as $status)
                    <option value="{{ $status->value }}" @selected($filters['status'] === $status->value)>{{ $status->label() }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex gap-2">
            <x-button variant="secondary"><x-heroicon-o-funnel class="size-4" aria-hidden="true" /> Filter</x-button>
            <x-button :href="route('hospital.appointments.index')" variant="ghost">Reset</x-button>
        </div>
    </form>

    <x-list-region :rows="8">
    @if ($appointments->isEmpty())
        <x-empty-state title="No appointments here" icon="heroicon-o-clipboard-document-check">
            @if ($filters['status'] === 'open' && empty($filters['search']) && empty($filters['date']))
                New bookings from parents will appear here for you to confirm.
            @else
                Try a different filter.
            @endif
        </x-empty-state>
    @else
        <x-card :padding="false" class="overflow-hidden">
            <div class="overflow-x-auto">
                <table class="table-stack min-w-full divide-y divide-line text-sm">
                    <caption class="sr-only">Appointments</caption>
                    <thead class="bg-surface-muted">
                        <tr>
                            <th scope="col" class="px-5 py-3 text-start font-semibold text-ink">When</th>
                            <th scope="col" class="px-5 py-3 text-start font-semibold text-ink">Child</th>
                            <th scope="col" class="px-5 py-3 text-start font-semibold text-ink">Vaccine</th>
                            <th scope="col" class="px-5 py-3 text-start font-semibold text-ink">Status</th>
                            <th scope="col" class="px-5 py-3 text-start font-semibold text-ink">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line">
                        @foreach ($appointments as $appointment)
                            <tr class="align-top hover:bg-canvas">
                                <td data-label="When" class="px-5 py-4">
                                    <p class="font-semibold text-ink">{{ $appointment->timeSlot->date->format('D j M') }}</p>
                                    <p class="text-ink-muted">{{ $appointment->timeSlot->timeRange() }}</p>
                                </td>
                                <td data-label="Child" class="px-5 py-4">
                                    <a href="{{ route('hospital.appointments.show', $appointment) }}" class="font-semibold text-ink hover:text-primary-700 hover:underline">
                                        {{ $appointment->child->fullName() }}
                                    </a>
                                    <p class="text-ink-muted">{{ $appointment->child->ageLabel() }} old</p>
                                </td>
                                <td data-label="Vaccine" class="px-5 py-4">
                                    <p class="font-medium text-ink">{{ $appointment->doseLabel() }}</p>
                                    <p class="font-mono text-xs text-ink-muted">Batch {{ $appointment->hospitalStock->batch_number }}</p>
                                </td>
                                <td data-label="Status" class="px-5 py-4">
                                    <x-status :status="$appointment->status" />
                                </td>
                                <td data-label="" class="px-5 py-4">
                                    @include('hospital.appointments.partials.actions', ['appointment' => $appointment])
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-card>

        <div class="mt-6">{{ $appointments->links() }}</div>
    @endif
    </x-list-region>
</x-app-layout>
