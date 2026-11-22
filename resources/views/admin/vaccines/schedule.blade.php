<x-app-layout title="Routine schedule">
    <x-page-header title="Routine schedule"
                   description="Every child's schedule is generated from this list when they are added.">
        <x-slot:breadcrumb>
            <a href="{{ route('admin.vaccines.index') }}" class="inline-flex items-center gap-1 font-medium text-primary-700 hover:underline">
                <x-heroicon-o-chevron-left class="size-4" aria-hidden="true" /> Vaccine catalog
            </a>
        </x-slot:breadcrumb>
    </x-page-header>

    <x-alert type="warning" title="Must be verified before real use" class="mb-6">
        This schedule was entered for development and has not been checked against the official EPI Pakistan schedule.
        It is maintained in <code class="font-mono">config/epi.php</code>; after changing it, run
        <code class="font-mono">php artisan db:seed --class=EpiScheduleSeeder</code>.
    </x-alert>

    @if ($byAge->isEmpty())
        <x-empty-state title="The schedule is empty" icon="heroicon-o-calendar-days">
            Run <code class="font-mono">php artisan db:seed --class=EpiScheduleSeeder</code> to load it.
        </x-empty-state>
    @else
        <x-card :padding="false" class="overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-line text-sm">
                    <caption class="sr-only">Routine vaccination schedule</caption>
                    <thead class="bg-surface-muted">
                        <tr>
                            <th scope="col" class="px-5 py-3 text-start font-semibold text-ink">Age</th>
                            <th scope="col" class="px-5 py-3 text-start font-semibold text-ink">Dose</th>
                            <th scope="col" class="px-5 py-3 text-start font-semibold text-ink">Vaccine</th>
                            <th scope="col" class="px-5 py-3 text-start font-semibold text-ink">Min. gap after previous dose</th>
                            <th scope="col" class="px-5 py-3 text-start font-semibold text-ink">Latest age</th>
                            <th scope="col" class="px-5 py-3 text-start font-semibold text-ink">Overdue after</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line">
                        @foreach ($byAge as $age => $doses)
                            @foreach ($doses as $dose)
                                <tr>
                                    @if ($loop->first)
                                        <th scope="rowgroup" rowspan="{{ $doses->count() }}" class="bg-canvas px-5 py-3 text-start align-top font-semibold text-ink">{{ $age }}</th>
                                    @endif
                                    <td class="px-5 py-3 font-medium text-ink">{{ $dose->label }}</td>
                                    <td class="px-5 py-3 text-ink-muted">{{ $dose->vaccine->name }}</td>
                                    <td class="px-5 py-3 text-ink">{{ $dose->dose_number > 1 ? $dose->min_interval_days.' days' : '—' }}</td>
                                    <td class="px-5 py-3 text-ink">{{ $dose->max_age_days !== null ? $dose->max_age_days.' days' : 'No limit' }}</td>
                                    <td class="px-5 py-3 text-ink">{{ $dose->grace_days }} days</td>
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-card>
    @endif
</x-app-layout>
