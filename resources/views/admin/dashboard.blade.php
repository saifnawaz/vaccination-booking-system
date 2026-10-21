<x-app-layout title="Overview">
    <x-page-header title="Overview" description="Vaccinations, hospitals and stock across the whole system." />

    {{-- KPI row: every number is counted from the database. --}}
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
        <x-stat label="Children registered" :value="$childrenRegistered" icon="heroicon-o-heart" />
        <x-stat label="Doses given this month" :value="$dosesThisMonth" icon="heroicon-o-check-circle" tone="success" />
        <x-stat label="Overdue doses" :value="$overdueDoses" icon="heroicon-o-exclamation-circle"
                :tone="$overdueDoses > 0 ? 'accent' : 'neutral'" />
        <x-stat label="Hospitals awaiting approval" :value="$pendingHospitals" icon="heroicon-o-clock"
                :tone="$pendingHospitals > 0 ? 'accent' : 'neutral'"
                :href="route('admin.hospitals.index', ['status' => 'pending'])" />
        <x-stat label="Stock warnings" :value="$stockWarnings['total']" icon="heroicon-o-archive-box"
                :tone="$stockWarnings['total'] > 0 ? 'accent' : 'neutral'" />
    </div>
    @if ($stockWarnings['total'] > 0)
        <p class="mt-2 text-sm text-ink-muted">
            Stock warnings at active hospitals: {{ $stockWarnings['low'] }} low-stock {{ str('vaccine')->plural($stockWarnings['low']) }},
            {{ $stockWarnings['expiring'] }} {{ str('batch')->plural($stockWarnings['expiring']) }} expiring within {{ config('vaccination.stock.expiring_soon_days') }} days,
            {{ $stockWarnings['expired'] }} expired {{ str('batch')->plural($stockWarnings['expired']) }} still holding doses.
        </p>
    @endif

    <div class="mt-8 grid gap-6 lg:grid-cols-2">
        <x-bar-chart title="Doses given per month" description="Given through appointments in the app, last 6 months."
                     :data="$dosesByMonth" unit="dose" empty="No doses have been given through the app yet." />

        <x-bar-chart title="Overdue doses by vaccine" description="Doses past their grace period that can still be given as catch-up."
                     orientation="row" :data="$overdueByVaccine" unit="dose" empty="No overdue doses. Every child is up to date." />

        <x-bar-chart title="Appointments this month" description="By status, for visits dated this month."
                     orientation="row" unit="appointment"
                     :data="collect($appointmentsByStatus)->map(fn ($r) => [...$r, 'icon' => match ($r['status']->value) {
                         'completed' => 'heroicon-o-check-circle', 'approved' => 'heroicon-o-calendar-days', 'pending' => 'heroicon-o-clock',
                         'rejected', 'no_show' => 'heroicon-o-x-circle', default => 'heroicon-o-minus-circle' }])->all()"
                     empty="No appointments this month yet." />

        <section aria-labelledby="recent-heading" class="rounded-2xl bg-surface p-5 shadow-sm ring-1 ring-line">
            <div class="flex items-end justify-between gap-4">
                <h3 id="recent-heading" class="text-base font-semibold text-ink">Recent activity</h3>
                <a href="{{ route('admin.activity.index') }}" class="text-sm font-semibold text-primary-700 hover:underline">Activity log</a>
            </div>
            @if ($recentActivity->isEmpty())
                <p class="mt-4 text-sm text-ink-muted">Approvals, cancellations and deletions will appear here.</p>
            @else
                <ul class="mt-3 divide-y divide-line">
                    @foreach ($recentActivity as $entry)
                        <li class="py-2.5 text-sm">
                            <p class="text-ink">{{ $entry->description }}</p>
                            <p class="text-xs text-ink-subtle">{{ $entry->causer?->name ?? 'System' }} · {{ $entry->created_at->diffForHumans() }}</p>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
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
