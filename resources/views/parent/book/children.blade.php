<x-app-layout title="Book a vaccination">
    <x-page-header title="Book a vaccination" description="Who is the appointment for?" />

    @if ($children->isEmpty())
        <x-empty-state title="Add a child first" icon="heroicon-o-heart">
            Once you've added a child, you can book the vaccines they're due for.
            <x-slot:actions>
                <x-button :href="route('parent.children.create')">Add a child</x-button>
            </x-slot:actions>
        </x-empty-state>
    @else
        <ul class="grid gap-3 sm:grid-cols-2">
            @foreach ($children as ['child' => $child, 'bookable' => $bookable])
                <li>
                    <a href="{{ route('parent.book.doses', $child) }}"
                       class="flex items-center justify-between gap-3 rounded-2xl bg-card p-5 shadow-sm ring-1 ring-line transition duration-200 ease-out hover:-translate-y-0.5 hover:shadow-md hover:ring-primary-300">
                        <span class="min-w-0">
                            <span class="block font-semibold text-ink">{{ $child->fullName() }}</span>
                            <span class="block text-sm text-ink-muted">{{ $child->ageLabel() }} old</span>
                            <span class="mt-2 block text-sm {{ $bookable ? 'font-medium text-primary-700' : 'text-ink-muted' }}">
                                {{ $bookable ? $bookable.' '.str('vaccine')->plural($bookable).' can be booked' : 'Nothing to book right now' }}
                            </span>
                        </span>
                        <x-heroicon-o-chevron-right class="size-5 shrink-0 text-ink-subtle" aria-hidden="true" />
                    </a>
                </li>
            @endforeach
        </ul>
    @endif
</x-app-layout>
