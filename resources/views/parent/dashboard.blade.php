<x-app-layout title="Home">
    <x-page-header :title="'Hello, '.\Illuminate\Support\Str::before($user->name, ' ')"
                   description="Your children's vaccinations, all in one place." />

    @if ($children->isEmpty())
        <x-empty-state title="No children added yet" icon="heroicon-o-heart">
            Add your child with their date of birth, and we'll build their full vaccination schedule automatically.
            <x-slot:actions>
                <x-button :href="route('parent.children.create')">Add your first child</x-button>
            </x-slot:actions>
        </x-empty-state>
    @else
        @if ($overdue > 0)
            <x-alert type="error" title="{{ $overdue }} {{ str('dose')->plural($overdue) }} overdue" class="mb-6">
                Please arrange these as soon as you can. Open a child below to see which ones.
            </x-alert>
        @elseif ($due > 0)
            <x-alert type="warning" title="{{ $due }} {{ str('dose')->plural($due) }} due now" class="mb-6">
                Open a child below to see which vaccines they need.
            </x-alert>
        @else
            <x-alert type="success" class="mb-6">Nothing is due right now. We'll show it here when the next dose is due.</x-alert>
        @endif

        <div class="mb-4 flex items-end justify-between gap-4">
            <h2 class="text-lg font-semibold text-ink">Your children</h2>
            <a href="{{ route('parent.children.create') }}" class="text-sm font-semibold text-primary-700 hover:underline">Add a child</a>
        </div>

        <ul class="grid gap-4 sm:grid-cols-2">
            @foreach ($children as $child)
                <li>@include('parent.children.partials.child-card', ['child' => $child, 'schedule' => $schedules[$child->id]])</li>
            @endforeach
        </ul>
    @endif
</x-app-layout>
