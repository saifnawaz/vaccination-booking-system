<x-app-layout title="Hospitals">
    <x-page-header title="Hospitals" description="Approve new registrations and manage which hospitals can take bookings." />

    <form method="GET" action="{{ route('admin.hospitals.index') }}" data-no-loading data-filter
          class="mb-6 flex flex-col gap-3 rounded-2xl bg-card p-4 ring-1 ring-line sm:flex-row sm:items-end">
        <div class="flex-1">
            <x-input-label for="search" value="Search" />
            <div class="relative mt-1.5">
                <x-heroicon-o-magnifying-glass class="pointer-events-none absolute top-1/2 left-3 size-5 -translate-y-1/2 text-ink-subtle" aria-hidden="true" />
                <x-text-input id="search" name="search" type="search" :value="$filters['search'] ?? ''"
                              placeholder="Name, city or email" class="ps-10" />
            </div>
        </div>
        <div class="sm:w-52">
            <x-input-label for="status" value="Status" />
            <select id="status" name="status"
                    class="mt-1.5 block w-full rounded-lg border-line-input bg-surface text-ink shadow-xs focus:border-primary-600 focus:ring-primary-600">
                <option value="">All statuses</option>
                @foreach ($statuses as $status)
                    <option value="{{ $status->value }}" @selected(($filters['status'] ?? null) === $status->value)>{{ $status->label() }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex gap-2">
            <x-button variant="secondary">
                <x-heroicon-o-funnel class="size-4" aria-hidden="true" /> Filter
            </x-button>
            @if (array_filter($filters))
                <x-button :href="route('admin.hospitals.index')" variant="ghost">Clear</x-button>
            @endif
        </div>
    </form>

    <x-list-region>
    @if ($hospitals->isEmpty())
        @if (array_filter($filters))
            <x-empty-state title="No hospitals match" icon="heroicon-o-magnifying-glass">
                Try a different search or status.
                <x-slot:actions>
                    <x-button :href="route('admin.hospitals.index')" variant="secondary">Clear filters</x-button>
                </x-slot:actions>
            </x-empty-state>
        @else
            <x-empty-state title="No hospitals yet" icon="heroicon-o-building-office-2">
                Hospitals appear here once they register.
            </x-empty-state>
        @endif
    @else
        <x-card :padding="false" class="overflow-hidden">
            <div class="overflow-x-auto">
                <table class="table-stack min-w-full divide-y divide-line text-sm">
                    <caption class="sr-only">Hospitals</caption>
                    <thead class="bg-surface-muted text-start">
                        <tr>
                            <th scope="col" class="px-5 py-3 text-start font-semibold text-ink">Hospital</th>
                            <th scope="col" class="px-5 py-3 text-start font-semibold text-ink">City</th>
                            <th scope="col" class="px-5 py-3 text-start font-semibold text-ink">Contact</th>
                            <th scope="col" class="px-5 py-3 text-start font-semibold text-ink">Status</th>
                            <th scope="col" class="px-5 py-3 text-end font-semibold text-ink"><span class="sr-only">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line">
                        @foreach ($hospitals as $hospital)
                            <tr class="hover:bg-canvas">
                                <td data-label="" class="px-5 py-4">
                                    <a href="{{ route('admin.hospitals.show', $hospital) }}" class="font-semibold text-ink hover:text-primary-700 hover:underline">
                                        {{ $hospital->name }}
                                    </a>
                                    <p class="text-ink-muted">Registered {{ $hospital->created_at->format('j M Y') }}</p>
                                </td>
                                <td data-label="City" class="px-5 py-4 text-ink">{{ $hospital->city }}</td>
                                <td data-label="Contact" class="px-5 py-4">
                                    <p class="text-ink">{{ $hospital->user->name }}</p>
                                    <p class="text-ink-muted">{{ $hospital->user->email }}</p>
                                </td>
                                <td data-label="Status" class="px-5 py-4">
                                    <x-status :status="$hospital->status" />
                                </td>
                                <td data-label="" class="px-5 py-4 text-end">
                                    <x-button :href="route('admin.hospitals.show', $hospital)" variant="ghost" size="sm">
                                        View <span class="sr-only">{{ $hospital->name }}</span>
                                        <x-heroicon-o-chevron-right class="size-4" aria-hidden="true" />
                                    </x-button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-card>

        <div class="mt-6">{{ $hospitals->links() }}</div>
    @endif
    </x-list-region>
</x-app-layout>
