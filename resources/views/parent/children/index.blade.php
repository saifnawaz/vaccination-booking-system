<x-app-layout title="Children">
    <x-page-header title="Children" description="Each child's schedule, worked out from their date of birth.">
        <x-slot:actions>
            <x-button :href="route('parent.children.create')">
                <x-heroicon-o-plus class="size-4" aria-hidden="true" /> Add a child
            </x-button>
        </x-slot:actions>
    </x-page-header>

    @if ($children->isEmpty())
        <x-empty-state title="No children added yet" icon="heroicon-o-heart">
            Add a child with their date of birth and we'll build their full vaccination schedule.
            <x-slot:actions>
                <x-button :href="route('parent.children.create')">Add your first child</x-button>
            </x-slot:actions>
        </x-empty-state>
    @else
        <ul class="grid gap-4 sm:grid-cols-2">
            @foreach ($children as $child)
                <li>@include('parent.children.partials.child-card', ['child' => $child, 'schedule' => $child->schedule()])</li>
            @endforeach
        </ul>
    @endif
</x-app-layout>
