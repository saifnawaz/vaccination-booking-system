<x-app-layout :title="'Adjust batch '.$batch->batch_number">
    <x-page-header :title="'Adjust batch '.$batch->batch_number" :description="$batch->vaccine->name.' · expires '.$batch->expiry_date->format('j M Y')">
        <x-slot:breadcrumb>
            <a href="{{ route('hospital.stock.index') }}" class="inline-flex items-center gap-1 font-medium text-primary-700 hover:underline">
                <x-heroicon-o-chevron-left class="size-4" aria-hidden="true" /> Stock
            </a>
        </x-slot:breadcrumb>
    </x-page-header>

    <x-card class="max-w-xl">
        <dl class="mb-6 grid grid-cols-3 gap-4 text-sm">
            <div>
                <dt class="font-medium text-ink-muted">On hand</dt>
                <dd class="text-xl font-bold tabular-nums text-ink">{{ number_format($batch->quantity) }}</dd>
            </div>
            <div>
                <dt class="font-medium text-ink-muted">Reserved</dt>
                <dd class="text-xl font-bold tabular-nums text-ink">{{ number_format($batch->reserved) }}</dd>
            </div>
            <div>
                <dt class="font-medium text-ink-muted">Available</dt>
                <dd class="text-xl font-bold tabular-nums text-ink">{{ number_format($batch->available()) }}</dd>
            </div>
        </dl>

        <form method="POST" action="{{ route('hospital.stock.update', $batch) }}" class="space-y-5">
            @csrf
            @method('PUT')

            <x-field name="quantity" label="Doses on hand" type="number" :value="$batch->quantity" required
                     :min="$batch->reserved" max="100000" inputmode="numeric" class="max-w-xs"
                     hint="Use this after a recount, or to record breakage or wastage. Set to 0 to empty the batch." />

            <div class="flex gap-3">
                <x-button>Save quantity</x-button>
                <x-button :href="route('hospital.stock.index')" variant="ghost">Cancel</x-button>
            </div>
        </form>
    </x-card>
</x-app-layout>
