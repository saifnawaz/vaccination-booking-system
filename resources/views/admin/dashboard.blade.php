<x-app-layout title="Overview">
    <x-page-header title="Overview" description="Hospitals and accounts across the system." />

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-stat label="Awaiting approval" :value="$pendingHospitals" icon="heroicon-o-clock"
                :tone="$pendingHospitals > 0 ? 'accent' : 'neutral'"
                :href="route('admin.hospitals.index', ['status' => 'pending'])" />
        <x-stat label="Active hospitals" :value="$activeHospitals" icon="heroicon-o-building-office-2" tone="success"
                :href="route('admin.hospitals.index', ['status' => 'active'])" />
        <x-stat label="Inactive hospitals" :value="$inactiveHospitals" icon="heroicon-o-building-office-2" tone="neutral"
                :href="route('admin.hospitals.index', ['status' => 'inactive'])" />
        <x-stat label="Registered parents" :value="$parents" icon="heroicon-o-users" />
    </div>

    <section aria-labelledby="awaiting-heading" class="mt-10">
        <div class="mb-4 flex items-end justify-between gap-4">
            <h2 id="awaiting-heading" class="text-lg font-semibold text-ink">Hospitals awaiting approval</h2>
            @if ($pendingHospitals > $awaitingApproval->count())
                <a href="{{ route('admin.hospitals.index', ['status' => 'pending']) }}" class="text-sm font-semibold text-primary-700 hover:underline">
                    View all {{ $pendingHospitals }}
                </a>
            @endif
        </div>

        @if ($awaitingApproval->isEmpty())
            <x-empty-state title="No hospitals waiting" icon="heroicon-o-check-circle">
                New hospital registrations will appear here for you to approve.
            </x-empty-state>
        @else
            <ul class="space-y-3">
                @foreach ($awaitingApproval as $hospital)
                    <li>
                        <x-card class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                            <div class="min-w-0">
                                <a href="{{ route('admin.hospitals.show', $hospital) }}" class="font-semibold text-ink hover:text-primary-700 hover:underline">
                                    {{ $hospital->name }}
                                </a>
                                <p class="mt-0.5 text-sm text-ink-muted">
                                    {{ $hospital->city }} · {{ $hospital->user->email }} ·
                                    registered {{ $hospital->created_at->diffForHumans() }}
                                </p>
                            </div>
                            @include('admin.hospitals.partials.status-actions', ['hospital' => $hospital])
                        </x-card>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>
</x-app-layout>
