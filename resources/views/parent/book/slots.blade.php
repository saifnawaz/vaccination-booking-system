<x-app-layout title="Choose a time">
    @php($doseIds = $doses->map(fn ($d) => $d->dose->id)->all())

    <x-page-header title="Choose a time" :description="$hospital->name.', '.$hospital->city">
        <x-slot:breadcrumb>
            <a href="{{ route('parent.book.hospitals', [$child, 'doses' => $doseIds]) }}" class="inline-flex items-center gap-1 font-medium text-primary-700 hover:underline">
                <x-heroicon-o-chevron-left class="size-4" aria-hidden="true" /> Other hospitals
            </a>
        </x-slot:breadcrumb>
    </x-page-header>

    <x-booking-steps :current="3" />

    @include('parent.book.partials.selected-doses')

    @if ($byDate->isEmpty())
        <x-empty-state title="No free times" icon="heroicon-o-calendar-days">
            This hospital has no free times when all these vaccines can be given. Try another hospital.
            <x-slot:actions>
                <x-button :href="route('parent.book.hospitals', [$child, 'doses' => $doseIds])" variant="secondary">Other hospitals</x-button>
            </x-slot:actions>
        </x-empty-state>
    @else
        <div class="space-y-6">
            @foreach ($byDate as $date => $slots)
                @php($day = \Carbon\CarbonImmutable::parse($date))
                <section aria-labelledby="day-{{ $date }}">
                    <h2 id="day-{{ $date }}" class="mb-3 font-semibold text-ink">
                        {{ $day->isToday() ? 'Today' : ($day->isTomorrow() ? 'Tomorrow' : $day->format('l')) }}
                        <span class="font-normal text-ink-muted">{{ $day->format('j F') }}</span>
                    </h2>
                    <ul class="grid grid-cols-2 gap-2 sm:grid-cols-4">
                        @foreach ($slots as $slot)
                            @php($left = $slot->capacity - $slot->booked_children)
                            <li>
                                <a href="{{ route('parent.book.confirm', [$child, 'doses' => $doseIds, 'slot' => $slot->id]) }}"
                                   class="block rounded-xl bg-surface px-3 py-3 text-center ring-1 ring-line-input transition hover:bg-primary-50 hover:ring-primary-400">
                                    <span class="block font-semibold text-ink">{{ \App\Models\TimeSlot::formatTime($slot->start_time) }}</span>
                                    <span class="block text-xs {{ $left <= 1 ? 'font-semibold text-accent-700' : 'text-ink-muted' }}">
                                        {{ $left }} {{ $left === 1 ? 'place' : 'places' }} left
                                    </span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endforeach
        </div>
    @endif
</x-app-layout>
