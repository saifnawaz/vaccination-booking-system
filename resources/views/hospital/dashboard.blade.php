<x-app-layout title="Overview">
    <x-page-header :title="$hospital->name" description="Your stock and opening times at a glance." />

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-stat label="Usable doses in stock" :value="$stock->usableDoses()" icon="heroicon-o-archive-box"
                :href="route('hospital.stock.index')" />
        <x-stat label="Vaccines running low" :value="$stock->lowStockCount()" icon="heroicon-o-exclamation-triangle"
                :tone="$stock->lowStockCount() > 0 ? 'accent' : 'neutral'" :href="route('hospital.stock.index')" />
        <x-stat label="Batches expiring soon" :value="$stock->expiringBatchCount()" icon="heroicon-o-clock"
                :tone="$stock->expiringBatchCount() > 0 ? 'accent' : 'neutral'"
                :href="route('hospital.stock.index', ['condition' => 'expiring'])" />
        <x-stat label="Upcoming time slots" :value="$upcomingSlotCount" icon="heroicon-o-calendar-days" tone="success"
                :href="route('hospital.slots.index')" />
    </div>

    <div class="mt-8 grid gap-6 lg:grid-cols-3">
        <div class="space-y-4 lg:col-span-2">
            <h2 class="text-lg font-semibold text-ink">Needs attention</h2>

            @php($attention = false)

            @if ($stock->expiredBatchCount() > 0)
                @php($attention = true)
                <x-alert type="error" title="{{ $stock->expiredBatchCount() }} expired {{ str('batch')->plural($stock->expiredBatchCount()) }} still on hand">
                    Expired doses are never offered for booking. Remove or set them to 0 once disposed of.
                    <a href="{{ route('hospital.stock.index', ['condition' => 'expired']) }}" class="font-semibold underline">Review expired batches</a>
                </x-alert>
            @endif

            @foreach ($stock->vaccines->where('low', true) as $row)
                @php($attention = true)
                <x-alert type="warning" title="{{ $row['vaccine']->name }} is running low">
                    {{ $row['usable'] }} usable {{ str('dose')->plural($row['usable']) }} left (warning below {{ config('vaccination.stock.low_threshold') }}).
                </x-alert>
            @endforeach

            @if ($upcomingSlotCount === 0)
                @php($attention = true)
                <x-alert type="warning" title="No upcoming time slots">
                    Parents can only book when you have open slots.
                    <a href="{{ route('hospital.slots.bulk') }}" class="font-semibold underline">Create slots in bulk</a>
                </x-alert>
            @endif

            @if ($stock->vaccines->isEmpty())
                @php($attention = true)
                <x-empty-state title="No vaccine stock yet" icon="heroicon-o-archive-box">
                    Add your first batch so parents can book with you.
                    <x-slot:actions>
                        <x-button :href="route('hospital.stock.create')">Add a batch</x-button>
                    </x-slot:actions>
                </x-empty-state>
            @endif

            @unless ($attention)
                <x-alert type="success">Everything looks good: stock is healthy and you have open slots.</x-alert>
            @endunless
        </div>

        <x-card>
            <div class="flex items-start justify-between gap-4">
                <h2 class="text-base font-semibold text-ink">Hospital details</h2>
                <x-badge :tone="$hospital->status->tone()">{{ $hospital->status->label() }}</x-badge>
            </div>
            <dl class="mt-4 space-y-3 text-sm">
                <div>
                    <dt class="font-medium text-ink-muted">Address</dt>
                    <dd class="text-ink">{{ $hospital->address }}, {{ $hospital->city }}</dd>
                </div>
                <div>
                    <dt class="font-medium text-ink-muted">Phone</dt>
                    <dd class="text-ink">{{ $hospital->phone }}</dd>
                </div>
                @if ($nextSlotDate)
                    <div>
                        <dt class="font-medium text-ink-muted">Next open day</dt>
                        <dd class="text-ink">{{ \Carbon\Carbon::parse($nextSlotDate)->format('l j M Y') }}</dd>
                    </div>
                @endif
            </dl>
            <x-button :href="route('hospital.profile.edit')" variant="secondary" size="sm" class="mt-5">Edit profile</x-button>
        </x-card>
    </div>
</x-app-layout>
