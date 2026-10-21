<x-app-layout title="Confirm booking">
    @php($doseIds = $doses->map(fn ($d) => $d->dose->id)->all())

    <x-page-header title="Check and confirm">
        <x-slot:breadcrumb>
            <a href="{{ route('parent.book.slots', [$child, $slot->hospital, 'doses' => $doseIds]) }}" class="inline-flex items-center gap-1 font-medium text-primary-700 hover:underline">
                <x-heroicon-o-chevron-left class="size-4" aria-hidden="true" /> Choose another time
            </a>
        </x-slot:breadcrumb>
    </x-page-header>

    <x-booking-steps :current="4" />

    <x-card class="max-w-xl">
        <dl class="space-y-4">
            <div>
                <dt class="text-sm font-medium text-ink-muted">Child</dt>
                <dd class="font-semibold text-ink">{{ $child->fullName() }} <span class="font-normal text-ink-muted">· {{ $child->ageLabel() }} old</span></dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-ink-muted">When</dt>
                <dd class="font-semibold text-ink">{{ $slot->date->format('l j F Y') }}, {{ $slot->timeRange() }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-ink-muted">Where</dt>
                <dd class="text-ink">
                    <span class="font-semibold">{{ $slot->hospital->name }}</span><br>
                    {{ $slot->hospital->address }}, {{ $slot->hospital->city }} · {{ $slot->hospital->phone }}
                </dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-ink-muted">Vaccines</dt>
                <dd>
                    <ul class="mt-1 space-y-1">
                        @foreach ($doses as $dose)
                            <li class="text-ink"><span class="font-semibold">{{ $dose->label() }}</span> <span class="text-ink-muted">· {{ $dose->schedule()->vaccine->name }}</span></li>
                        @endforeach
                    </ul>
                </dd>
            </div>
        </dl>

        @php($cutoff = config('vaccination.booking.change_cutoff_hours'))
        @if (now()->gt($slot->startsAt()->subHours($cutoff)))
            <x-alert type="warning" class="mt-6">
                This appointment is less than {{ $cutoff }} hours away, so you won't be able to cancel or change it online.
                If plans change, please phone the hospital.
            </x-alert>
        @else
            <x-alert type="info" class="mt-6">
                The hospital will confirm your booking. You can cancel or change it here up to
                {{ $cutoff }} hours before the appointment.
            </x-alert>
        @endif

        <form method="POST" action="{{ route('parent.appointments.store') }}" class="mt-6">
            @csrf
            <input type="hidden" name="child" value="{{ $child->id }}">
            <input type="hidden" name="slot" value="{{ $slot->id }}">
            @foreach ($doseIds as $id)
                <input type="hidden" name="doses[]" value="{{ $id }}">
            @endforeach
            <x-button size="lg" class="w-full">Confirm booking</x-button>
        </form>
    </x-card>
</x-app-layout>
