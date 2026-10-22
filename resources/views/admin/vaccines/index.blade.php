<x-app-layout title="Vaccines">
    <x-page-header title="Vaccine catalog" description="The vaccines hospitals can stock and parents can book.">
        <x-slot:actions>
            <x-button :href="route('admin.vaccines.schedule')" variant="secondary">
                <x-heroicon-o-calendar-days class="size-4" aria-hidden="true" /> Routine schedule
            </x-button>
            <x-button :href="route('admin.vaccines.create')">
                <x-heroicon-o-plus class="size-4" aria-hidden="true" /> Add vaccine
            </x-button>
        </x-slot:actions>
    </x-page-header>

    <form method="GET" action="{{ route('admin.vaccines.index') }}" data-no-loading data-filter
          class="mb-6 flex flex-col gap-3 rounded-2xl bg-card p-4 ring-1 ring-line sm:flex-row sm:items-end">
        <div class="flex-1">
            <x-input-label for="search" value="Search" />
            <x-text-input id="search" name="search" type="search" :value="$filters['search'] ?? ''" placeholder="Name or code" class="mt-1.5" />
        </div>
        <div class="sm:w-48">
            <x-input-label for="status" value="Status" />
            <select id="status" name="status" class="mt-1.5 block w-full rounded-lg border-line-input bg-surface text-ink shadow-xs focus:border-primary-600 focus:ring-primary-600">
                <option value="">All</option>
                <option value="active" @selected(($filters['status'] ?? null) === 'active')>Active</option>
                <option value="inactive" @selected(($filters['status'] ?? null) === 'inactive')>Inactive</option>
            </select>
        </div>
        <div class="flex gap-2">
            <x-button variant="secondary"><x-heroicon-o-funnel class="size-4" aria-hidden="true" /> Filter</x-button>
            @if (array_filter($filters))
                <x-button :href="route('admin.vaccines.index')" variant="ghost">Clear</x-button>
            @endif
        </div>
    </form>

    <x-list-region>
    @if ($vaccines->isEmpty())
        <x-empty-state :title="array_filter($filters) ? 'No vaccines match' : 'No vaccines yet'" icon="heroicon-o-beaker">
            @if (array_filter($filters))
                Try a different search.
            @else
                Run the schedule seeder or add a vaccine to get started.
            @endif
        </x-empty-state>
    @else
        <x-card :padding="false" class="overflow-hidden">
            <div class="overflow-x-auto">
                <table class="table-stack min-w-full divide-y divide-line text-sm">
                    <caption class="sr-only">Vaccines</caption>
                    <thead class="bg-surface-muted">
                        <tr>
                            <th scope="col" class="px-5 py-3 text-start font-semibold text-ink">Vaccine</th>
                            <th scope="col" class="px-5 py-3 text-start font-semibold text-ink">Code</th>
                            <th scope="col" class="px-5 py-3 text-start font-semibold text-ink">In schedule</th>
                            <th scope="col" class="px-5 py-3 text-start font-semibold text-ink">Status</th>
                            <th scope="col" class="px-5 py-3 text-end"><span class="sr-only">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line">
                        @foreach ($vaccines as $vaccine)
                            <tr class="hover:bg-canvas">
                                <td data-label="" class="px-5 py-4">
                                    <p class="font-semibold text-ink">{{ $vaccine->name }}</p>
                                    @if ($vaccine->description)
                                        <p class="max-w-md text-ink-muted">{{ $vaccine->description }}</p>
                                    @endif
                                </td>
                                <td data-label="Code" class="px-5 py-4 font-mono text-ink">{{ $vaccine->code }}</td>
                                <td data-label="In schedule" class="px-5 py-4 text-ink">
                                    {{ $vaccine->schedule_doses_count ? $vaccine->schedule_doses_count.' '.str('dose')->plural($vaccine->schedule_doses_count) : 'No' }}
                                </td>
                                <td data-label="Status" class="px-5 py-4">
                                    <x-badge :tone="$vaccine->is_active ? 'success' : 'neutral'">{{ $vaccine->is_active ? 'Active' : 'Inactive' }}</x-badge>
                                </td>
                                <td data-label="" class="px-5 py-4 text-end">
                                    <x-button :href="route('admin.vaccines.edit', $vaccine)" variant="ghost" size="sm">
                                        Edit <span class="sr-only">{{ $vaccine->name }}</span>
                                    </x-button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-card>

        <div class="mt-6">{{ $vaccines->links() }}</div>
    @endif
    </x-list-region>
</x-app-layout>
