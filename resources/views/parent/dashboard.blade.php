<x-app-layout title="Home">
    <x-page-header :title="'Hello, '.\Illuminate\Support\Str::before($user->name, ' ')"
                   description="Your children's vaccinations, all in one place." />

    @if ($children->isEmpty())
        <x-empty-state title="No children added yet" icon="heroicon-o-heart">
            Add your child with their date of birth, and we'll build their full vaccination schedule automatically.
            <x-slot:actions>
                <x-button :href="route('parent.children.create')">Add your first child</x-button>
            </x-slot:actions>
        </x-empty-state>
    @else
        @if ($overdue > 0)
            <x-alert type="error" title="{{ $overdue }} {{ str('dose')->plural($overdue) }} overdue" class="mb-6">
                Please arrange these as soon as you can. Late doses can still be given as catch-up.
                <a href="{{ route('parent.book') }}" class="font-semibold underline">Book an appointment</a>
            </x-alert>
        @elseif ($due > 0)
            <x-alert type="warning" title="{{ $due }} {{ str('dose')->plural($due) }} due now" class="mb-6">
                <a href="{{ route('parent.book') }}" class="font-semibold underline">Book an appointment</a>
            </x-alert>
        @else
            <x-alert type="success" class="mb-6">Nothing is due right now. We'll show it here when the next dose is due.</x-alert>
        @endif

        @if ($upcoming->isNotEmpty())
            <section aria-labelledby="next-visits" class="mb-8">
                <div class="mb-3 flex items-end justify-between gap-4">
                    <h2 id="next-visits" class="text-lg font-semibold text-ink">Next appointments</h2>
                    <a href="{{ route('parent.appointments.index') }}" class="text-sm font-semibold text-primary-700 hover:underline">All appointments</a>
                </div>
                <ul class="space-y-3">
                    @foreach ($upcoming as $visit)
                        @php($first = $visit->first())
                        <li class="rounded-2xl bg-surface p-4 shadow-sm ring-1 ring-line">
                            <p class="font-semibold text-ink">{{ $first->timeSlot->date->format('D j M') }}, {{ \App\Models\TimeSlot::formatTime($first->timeSlot->start_time) }}
                                <span class="font-normal text-ink-muted">· {{ $first->child->first_name }}</span></p>
                            <p class="text-sm text-ink-muted">{{ $first->hospital->name }}</p>
                            <div class="mt-2 flex flex-wrap gap-2">
                                @foreach ($visit as $appointment)
                                    <x-badge :tone="$appointment->status->tone()">{{ $appointment->doseLabel() }}: {{ $appointment->status->label() }}</x-badge>
                                @endforeach
                            </div>
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif

        <div class="mb-4 flex items-end justify-between gap-4">
            <h2 class="text-lg font-semibold text-ink">Your children</h2>
            <a href="{{ route('parent.children.create') }}" class="text-sm font-semibold text-primary-700 hover:underline">Add a child</a>
        </div>

        <ul class="grid gap-4 sm:grid-cols-2">
            @foreach ($children as $child)
                <li>@include('parent.children.partials.child-card', ['child' => $child, 'schedule' => $schedules[$child->id]])</li>
            @endforeach
        </ul>
    @endif
</x-app-layout>
