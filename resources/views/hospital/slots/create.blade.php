<x-app-layout title="Add a slot">
    <x-page-header title="Add one slot">
        <x-slot:breadcrumb>
            <a href="{{ route('hospital.slots.index') }}" class="inline-flex items-center gap-1 font-medium text-primary-700 hover:underline">
                <x-heroicon-o-chevron-left class="size-4" aria-hidden="true" /> Time slots
            </a>
        </x-slot:breadcrumb>
    </x-page-header>

    <x-card class="max-w-xl">
        <form method="POST" action="{{ route('hospital.slots.store') }}" class="space-y-5">
            @csrf

            <x-field name="date" label="Date" type="date" required :min="today()->toDateString()" />

            <div class="grid gap-5 sm:grid-cols-2">
                <x-field name="start_time" label="Starts" type="time" required />
                <x-field name="end_time" label="Ends" type="time" required />
            </div>

            <x-field name="capacity" label="Children per slot" type="number" required min="1"
                     :max="config('vaccination.slots.max_capacity')" value="4" inputmode="numeric" class="max-w-xs" />

            <div class="flex gap-3">
                <x-button>Add slot</x-button>
                <x-button :href="route('hospital.slots.index')" variant="ghost">Cancel</x-button>
            </div>
        </form>
    </x-card>
</x-app-layout>
