<x-app-layout title="Activity log">
    <x-page-header title="Activity log" description="Who approved, declined, cancelled, changed or deleted what." />

    <form method="GET" action="{{ route('admin.activity.index') }}" data-no-loading data-filter
          class="mb-6 grid gap-3 rounded-2xl bg-card p-4 ring-1 ring-line sm:grid-cols-2 lg:grid-cols-[1fr_12rem_10rem_10rem_auto] lg:items-end">
        <div>
            <x-input-label for="search" value="Search" />
            <x-text-input id="search" name="search" type="search" :value="$filters['search'] ?? ''" placeholder="Name, hospital, child…" class="mt-1.5" />
        </div>
        <div>
            <x-input-label for="area" value="Area" />
            <select id="area" name="area" class="mt-1.5 block w-full rounded-lg border-line-input bg-surface text-ink shadow-xs focus:border-primary-600 focus:ring-primary-600">
                <option value="">Everything</option>
                @foreach ($areas as $value => $label)
                    <option value="{{ $value }}" @selected(($filters['area'] ?? null) === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <x-input-label for="from" value="From" />
            <x-text-input id="from" name="from" type="date" :value="$filters['from'] ?? ''" class="mt-1.5" />
        </div>
        <div>
            <x-input-label for="to" value="To" />
            <x-text-input id="to" name="to" type="date" :value="$filters['to'] ?? ''" class="mt-1.5" />
        </div>
        <div class="flex gap-2">
            <x-button variant="secondary"><x-heroicon-o-funnel class="size-4" aria-hidden="true" /> Filter</x-button>
            @if (array_filter($filters))
                <x-button :href="route('admin.activity.index')" variant="ghost">Clear</x-button>
            @endif
        </div>
    </form>

    <x-list-region :rows="8">
    @if ($entries->isEmpty())
        <x-empty-state :title="array_filter($filters) ? 'Nothing matches' : 'No activity yet'" icon="heroicon-o-clipboard-document-check">
            {{ array_filter($filters) ? 'Try a different search or date range.' : 'Approvals, cancellations and deletions will appear here.' }}
        </x-empty-state>
    @else
        <x-card :padding="false" class="overflow-hidden">
            <div class="overflow-x-auto">
                <table class="table-stack min-w-full divide-y divide-line text-sm">
                    <caption class="sr-only">Activity log</caption>
                    <thead class="bg-surface-muted">
                        <tr>
                            <th scope="col" class="px-5 py-3 text-start font-semibold text-ink">When</th>
                            <th scope="col" class="px-5 py-3 text-start font-semibold text-ink">Who</th>
                            <th scope="col" class="px-5 py-3 text-start font-semibold text-ink">What</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line">
                        @foreach ($entries as $entry)
                            <tr class="align-top">
                                <td data-label="When" class="whitespace-nowrap px-5 py-3">
                                    <p class="text-ink">{{ $entry->created_at->format('j M Y') }}</p>
                                    <p class="text-ink-muted">{{ $entry->created_at->format('g:i a') }}</p>
                                </td>
                                <td data-label="Who" class="px-5 py-3">
                                    @if ($entry->causer)
                                        <p class="font-medium text-ink">{{ $entry->causer->name }}</p>
                                        <p class="text-ink-muted">{{ $entry->causer->role->label() }}</p>
                                    @else
                                        <p class="font-medium text-ink">System</p>
                                        <p class="text-ink-muted">Automatic</p>
                                    @endif
                                </td>
                                <td data-label="What" class="px-5 py-3">
                                    <x-badge tone="neutral">{{ $areas[$entry->area()] ?? ucfirst($entry->area()) }}</x-badge>
                                    <p class="mt-1 text-ink">{{ $entry->description }}</p>
                                    @if (! empty($entry->properties['reason']))
                                        <p class="mt-0.5 text-ink-muted">Reason: {{ $entry->properties['reason'] }}</p>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-card>

        <div class="mt-6">{{ $entries->links() }}</div>
    @endif
    </x-list-region>
</x-app-layout>
