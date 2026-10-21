<x-app-layout title="Stock">
    <x-page-header title="Vaccine stock" description="Your batches, with what's usable, reserved and about to expire.">
        <x-slot:actions>
            <x-button :href="route('hospital.stock.create')">
                <x-heroicon-o-plus class="size-4" aria-hidden="true" /> Add batch
            </x-button>
        </x-slot:actions>
    </x-page-header>

    @if ($summary->vaccines->isEmpty())
        <x-empty-state title="No stock yet" icon="heroicon-o-archive-box">
            Add a batch with its number, expiry date and quantity. Parents can only book vaccines you have in stock.
            <x-slot:actions>
                <x-button :href="route('hospital.stock.create')">Add your first batch</x-button>
            </x-slot:actions>
        </x-empty-state>
    @else
        {{-- Per-vaccine totals --}}
        <section aria-labelledby="by-vaccine" class="mb-8">
            <h2 id="by-vaccine" class="mb-3 text-lg font-semibold text-ink">By vaccine</h2>
            <ul class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($summary->vaccines as $row)
                    <li @class([
                        'rounded-2xl p-4 ring-1',
                        'bg-accent-50 ring-accent-200' => $row['low'],
                        'bg-surface ring-line' => ! $row['low'],
                    ])>
                        <div class="flex items-start justify-between gap-3">
                            <p class="font-semibold text-ink">{{ $row['vaccine']->name }}</p>
                            @if ($row['low'])
                                <x-badge tone="accent">Low stock</x-badge>
                            @endif
                        </div>
                        <p class="mt-2 text-2xl font-bold tabular-nums text-ink">{{ number_format($row['usable']) }}
                            <span class="text-sm font-medium text-ink-muted">usable {{ str('dose')->plural($row['usable']) }}</span>
                        </p>
                        <p class="mt-1 text-sm text-ink-muted">
                            {{ $row['batches'] }} {{ str('batch')->plural($row['batches']) }}
                            @if ($row['expiring']) · <span class="font-semibold text-warning-800">{{ $row['expiring'] }} expiring soon</span> @endif
                            @if ($row['expired']) · <span class="font-semibold text-danger-700">{{ $row['expired'] }} expired</span> @endif
                        </p>
                    </li>
                @endforeach
            </ul>
        </section>

        {{-- Batches --}}
        <section aria-labelledby="batches">
            <h2 id="batches" class="mb-3 text-lg font-semibold text-ink">Batches</h2>

            <form method="GET" action="{{ route('hospital.stock.index') }}" data-no-loading
                  class="mb-4 flex flex-col gap-3 rounded-2xl bg-surface p-4 ring-1 ring-line sm:flex-row sm:items-end">
                <div class="flex-1">
                    <x-input-label for="search" value="Batch number" />
                    <x-text-input id="search" name="search" type="search" :value="$filters['search'] ?? ''" class="mt-1.5" />
                </div>
                <div class="sm:w-56">
                    <x-input-label for="vaccine" value="Vaccine" />
                    <select id="vaccine" name="vaccine" class="mt-1.5 block w-full rounded-lg border-line-input bg-surface text-ink shadow-xs focus:border-primary-600 focus:ring-primary-600">
                        <option value="">All vaccines</option>
                        @foreach ($vaccines as $vaccine)
                            <option value="{{ $vaccine->id }}" @selected((int) ($filters['vaccine'] ?? 0) === $vaccine->id)>{{ $vaccine->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="sm:w-48">
                    <x-input-label for="condition" value="Condition" />
                    <select id="condition" name="condition" class="mt-1.5 block w-full rounded-lg border-line-input bg-surface text-ink shadow-xs focus:border-primary-600 focus:ring-primary-600">
                        <option value="">Any</option>
                        <option value="usable" @selected(($filters['condition'] ?? null) === 'usable')>Usable</option>
                        <option value="expiring" @selected(($filters['condition'] ?? null) === 'expiring')>Expiring soon</option>
                        <option value="expired" @selected(($filters['condition'] ?? null) === 'expired')>Expired</option>
                    </select>
                </div>
                <div class="flex gap-2">
                    <x-button variant="secondary"><x-heroicon-o-funnel class="size-4" aria-hidden="true" /> Filter</x-button>
                    @if (array_filter($filters))
                        <x-button :href="route('hospital.stock.index')" variant="ghost">Clear</x-button>
                    @endif
                </div>
            </form>

            @if ($batches->isEmpty())
                <x-empty-state title="No batches match" icon="heroicon-o-magnifying-glass">Try a different filter.</x-empty-state>
            @else
                <x-card :padding="false" class="overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-line text-sm">
                            <caption class="sr-only">Stock batches</caption>
                            <thead class="bg-surface-muted">
                                <tr>
                                    <th scope="col" class="px-5 py-3 text-start font-semibold text-ink">Vaccine and batch</th>
                                    <th scope="col" class="px-5 py-3 text-start font-semibold text-ink">Expires</th>
                                    <th scope="col" class="px-5 py-3 text-end font-semibold text-ink">On hand</th>
                                    <th scope="col" class="px-5 py-3 text-end font-semibold text-ink">Reserved</th>
                                    <th scope="col" class="px-5 py-3 text-end font-semibold text-ink">Available</th>
                                    <th scope="col" class="px-5 py-3 text-end"><span class="sr-only">Actions</span></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-line">
                                @foreach ($batches as $batch)
                                    <tr @class(['hover:bg-canvas', 'bg-danger-50/50' => $batch->isExpired()])>
                                        <td class="px-5 py-4">
                                            <p class="font-semibold text-ink">{{ $batch->vaccine->name }}</p>
                                            <p class="font-mono text-ink-muted">{{ $batch->batch_number }}</p>
                                        </td>
                                        <td class="px-5 py-4">
                                            <p class="text-ink">{{ $batch->expiry_date->format('j M Y') }}</p>
                                            @if ($batch->isExpired())
                                                <x-badge tone="danger" class="mt-1">Expired</x-badge>
                                            @elseif ($batch->isExpiringSoon())
                                                <x-badge tone="warning" class="mt-1">Expires in {{ (int) today()->diffInDays($batch->expiry_date) }} days</x-badge>
                                            @endif
                                        </td>
                                        <td class="px-5 py-4 text-end tabular-nums text-ink">{{ number_format($batch->quantity) }}</td>
                                        <td class="px-5 py-4 text-end tabular-nums text-ink-muted">{{ number_format($batch->reserved) }}</td>
                                        <td class="px-5 py-4 text-end font-semibold tabular-nums {{ $batch->isExpired() ? 'text-ink-subtle line-through' : 'text-ink' }}">
                                            {{ number_format($batch->available()) }}
                                            @if ($batch->isExpired()) <span class="sr-only">(expired, not usable)</span> @endif
                                        </td>
                                        <td class="px-5 py-4">
                                            <div class="flex justify-end gap-1">
                                                <x-button :href="route('hospital.stock.edit', $batch)" variant="ghost" size="sm">
                                                    Adjust <span class="sr-only">batch {{ $batch->batch_number }}</span>
                                                </x-button>
                                                @if ($batch->isRemovable())
                                                    <x-button type="button" variant="ghost" size="sm" class="text-danger-700 hover:bg-danger-50"
                                                              x-data x-on:click="$dispatch('open-modal', 'remove-batch-{{ $batch->id }}')">
                                                        Remove <span class="sr-only">batch {{ $batch->batch_number }}</span>
                                                    </x-button>
                                                    <x-confirm-dialog :name="'remove-batch-'.$batch->id" :title="'Remove batch '.$batch->batch_number.'?'"
                                                                      :action="route('hospital.stock.destroy', $batch)" method="DELETE" confirm-label="Remove batch">
                                                        {{ number_format($batch->quantity) }} doses of {{ $batch->vaccine->name }} will be removed from your stock.
                                                    </x-confirm-dialog>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </x-card>

                <div class="mt-6">{{ $batches->links() }}</div>
            @endif
        </section>
    @endif
</x-app-layout>
