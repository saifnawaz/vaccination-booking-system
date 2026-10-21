<x-app-layout title="Change slot">
    <x-page-header title="Change capacity"
                   :description="$slot->date->format('l j F Y').', '.$slot->timeRange()">
        <x-slot:breadcrumb>
            <a href="{{ route('hospital.slots.index', ['from' => $slot->date->toDateString()]) }}" class="inline-flex items-center gap-1 font-medium text-primary-700 hover:underline">
                <x-heroicon-o-chevron-left class="size-4" aria-hidden="true" /> Time slots
            </a>
        </x-slot:breadcrumb>
    </x-page-header>

    <x-card class="max-w-xl">
        <form method="POST" action="{{ route('hospital.slots.update', $slot) }}" class="space-y-5">
            @csrf
            @method('PUT')

            <x-field name="capacity" label="Children per slot" type="number" :value="$slot->capacity" required min="1"
                     :max="config('vaccination.slots.max_capacity')" inputmode="numeric" class="max-w-xs"
                     hint="To change the time, remove this slot and add a new one." />

            <div class="flex gap-3">
                <x-button>Save</x-button>
                <x-button :href="route('hospital.slots.index', ['from' => $slot->date->toDateString()])" variant="ghost">Cancel</x-button>
            </div>
        </form>
    </x-card>
</x-app-layout>
