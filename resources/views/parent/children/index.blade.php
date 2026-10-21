<x-app-layout title="Children">
    <x-page-header title="Children" description="Each child's schedule, worked out from their date of birth.">
        <x-slot:actions>
            <x-button :href="route('parent.children.create')">
                <x-heroicon-o-plus class="size-4" aria-hidden="true" /> Add a child
            </x-button>
        </x-slot:actions>
    </x-page-header>

    @if ($total > 3)
        <form method="GET" action="{{ route('parent.children.index') }}" data-no-loading class="mb-6 flex gap-2">
            <x-input-label for="search" value="Search children" class="sr-only" />
            <x-text-input id="search" name="search" type="search" :value="$search" placeholder="Child's name" class="max-w-sm" />
            <x-button variant="secondary">Search</x-button>
            @if ($search)
                <x-button :href="route('parent.children.index')" variant="ghost">Clear</x-button>
            @endif
        </form>
    @endif

    @if ($total === 0)
        <x-empty-state title="No children added yet" icon="heroicon-o-heart">
            Add a child with their date of birth and we'll build their full vaccination schedule.
            <x-slot:actions>
                <x-button :href="route('parent.children.create')">Add your first child</x-button>
            </x-slot:actions>
        </x-empty-state>
    @elseif ($children->isEmpty())
        <x-empty-state title="No children match" icon="heroicon-o-magnifying-glass">
            Nobody called "{{ $search }}".
            <x-slot:actions>
                <x-button :href="route('parent.children.index')" variant="secondary">Show all children</x-button>
            </x-slot:actions>
        </x-empty-state>
    @else
        <ul class="grid gap-4 sm:grid-cols-2">
            @foreach ($children as $child)
                <li>@include('parent.children.partials.child-card', ['child' => $child, 'schedule' => $child->schedule()])</li>
            @endforeach
        </ul>

        <div class="mt-6">{{ $children->links() }}</div>
    @endif
</x-app-layout>
