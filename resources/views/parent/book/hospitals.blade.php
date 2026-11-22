<x-app-layout title="Choose a hospital">
    @php($doseQuery = ['doses' => $doses->map(fn ($d) => $d->dose->id)->all()])

    <x-page-header title="Choose a hospital" :description="'For '.$child->fullName()" />

    <x-booking-steps :current="2" />

    @include('parent.book.partials.selected-doses')

    <form method="GET" action="{{ route('parent.book.hospitals', $child) }}" data-no-loading class="mb-6 flex gap-2">
        @foreach ($doseQuery['doses'] as $id)
            <input type="hidden" name="doses[]" value="{{ $id }}">
        @endforeach
        <x-input-label for="search" value="Search hospitals" class="sr-only" />
        <x-text-input id="search" name="search" type="search" :value="$search" placeholder="Hospital name or city" />
        <x-button variant="secondary">Search</x-button>
    </form>

    @if ($hospitals->isEmpty())
        <x-empty-state title="No hospital can take this booking" icon="heroicon-o-building-office-2">
            @if ($search)
                No hospital matching "{{ $search }}" has all these vaccines in stock with a free time.
            @else
                No hospital currently has all of these vaccines in stock with a free time
                @if ($window[1]) before {{ $window[1]->format('j M Y') }} @endif.
                Try booking fewer vaccines together.
            @endif
            <x-slot:actions>
                <x-button :href="route('parent.book.doses', $child)" variant="secondary">Change vaccines</x-button>
            </x-slot:actions>
        </x-empty-state>
    @else
        <ul class="space-y-3">
            @foreach ($hospitals as $hospital)
                <li>
                    <a href="{{ route('parent.book.slots', [$child, $hospital, ...$doseQuery]) }}"
                       class="flex items-center justify-between gap-4 rounded-2xl bg-surface p-5 shadow-sm ring-1 ring-line transition hover:ring-primary-300">
                        <span class="min-w-0">
                            <span class="block font-semibold text-ink">{{ $hospital->name }}</span>
                            <span class="mt-0.5 flex items-center gap-1 text-sm text-ink-muted">
                                <x-heroicon-o-map-pin class="size-4 shrink-0" aria-hidden="true" /> {{ $hospital->address }}, {{ $hospital->city }}
                            </span>
                            <span class="mt-2 block text-sm font-medium text-success-700">
                                All vaccines in stock · next free time {{ \Carbon\Carbon::parse($hospital->next_slot_date)->format('D j M') }}
                            </span>
                        </span>
                        <x-heroicon-o-chevron-right class="size-5 shrink-0 text-ink-subtle" aria-hidden="true" />
                    </a>
                </li>
            @endforeach
        </ul>
    @endif
</x-app-layout>
