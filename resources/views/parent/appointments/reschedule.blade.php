<x-app-layout title="Change appointment time">
    <x-page-header title="Choose a new time"
                   :description="$appointment->doseLabel().' for '.$appointment->child->first_name.' at '.$appointment->hospital->name">
        <x-slot:breadcrumb>
            <a href="{{ route('parent.appointments.index') }}" class="inline-flex items-center gap-1 font-medium text-primary-700 hover:underline">
                <x-heroicon-o-chevron-left class="size-4" aria-hidden="true" /> Appointments
            </a>
        </x-slot:breadcrumb>
    </x-page-header>

    <x-alert type="info" class="mb-6">
        Currently booked for <strong>{{ $appointment->timeSlot->date->format('l j F') }}, {{ $appointment->timeSlot->timeRange() }}</strong>.
        After you change it, the hospital will confirm the new time.
    </x-alert>

    @if ($byDate->isEmpty())
        <x-empty-state title="No other free times" icon="heroicon-o-calendar-days">
            {{ $appointment->hospital->name }} has no other free time when this vaccine can be given.
            You can cancel this appointment and book at another hospital instead.
        </x-empty-state>
    @else
        <div class="space-y-6">
            @foreach ($byDate as $date => $slots)
                @php($day = \Carbon\CarbonImmutable::parse($date))
                <section aria-labelledby="day-{{ $date }}">
                    <h2 id="day-{{ $date }}" class="mb-3 font-semibold text-ink">
                        {{ $day->format('l') }} <span class="font-normal text-ink-muted">{{ $day->format('j F') }}</span>
                    </h2>
                    <ul class="grid grid-cols-2 gap-2 sm:grid-cols-4">
                        @foreach ($slots as $slot)
                            @php($left = $slot->capacity - $slot->booked_children)
                            <li>
                                <form method="POST" action="{{ route('parent.appointments.reschedule.update', $appointment) }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="slot" value="{{ $slot->id }}">
                                    <button type="submit" class="group w-full rounded-xl bg-surface px-3 py-3 text-center ring-1 ring-line-input transition hover:bg-primary-50 hover:ring-primary-400 disabled:opacity-60">
                                        <span class="block font-semibold text-ink">{{ \App\Models\TimeSlot::formatTime($slot->start_time) }}</span>
                                        <span class="block text-xs text-ink-muted">{{ $left }} {{ $left === 1 ? 'place' : 'places' }} left</span>
                                        <span class="sr-only">, {{ $day->format('l j F') }}: move appointment here</span>
                                    </button>
                                </form>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endforeach
        </div>
    @endif
</x-app-layout>
