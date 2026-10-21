<x-app-layout title="Add batch">
    <x-page-header title="Add a batch" description="Enter the details from the vaccine packaging.">
        <x-slot:breadcrumb>
            <a href="{{ route('hospital.stock.index') }}" class="inline-flex items-center gap-1 font-medium text-primary-700 hover:underline">
                <x-heroicon-o-chevron-left class="size-4" aria-hidden="true" /> Stock
            </a>
        </x-slot:breadcrumb>
    </x-page-header>

    <x-card class="max-w-2xl">
        <form method="POST" action="{{ route('hospital.stock.store') }}" class="space-y-5">
            @csrf

            <x-select-field name="vaccine_id" label="Vaccine" required placeholder="Choose a vaccine…"
                            :options="$vaccines->pluck('name', 'id')->all()" />

            <div class="grid gap-5 sm:grid-cols-2">
                <x-field name="batch_number" label="Batch number" required class="font-mono uppercase" autocomplete="off" />
                <x-field name="expiry_date" label="Expiry date" type="date" required :min="today()->addDay()->toDateString()"
                         hint="The batch can be used up to and including this date." />
            </div>

            <x-field name="quantity" label="Number of doses" type="number" required min="1" max="100000" inputmode="numeric" class="max-w-xs" />

            <div class="flex gap-3">
                <x-button>Add batch</x-button>
                <x-button :href="route('hospital.stock.index')" variant="ghost">Cancel</x-button>
            </div>
        </form>
    </x-card>
</x-app-layout>
