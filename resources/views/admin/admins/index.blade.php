<x-app-layout title="Administrators">
    <x-page-header title="Administrators" description="People who can approve hospitals and manage the system.">
        <x-slot:actions>
            <x-button :href="route('admin.admins.create')">
                <x-heroicon-o-plus class="size-4" aria-hidden="true" /> Add administrator
            </x-button>
        </x-slot:actions>
    </x-page-header>

    <form method="GET" action="{{ route('admin.admins.index') }}" data-no-loading class="mb-6 flex gap-2">
        <x-input-label for="search" value="Search administrators" class="sr-only" />
        <x-text-input id="search" name="search" type="search" :value="$search" placeholder="Name or email" class="max-w-sm" />
        <x-button variant="secondary">Search</x-button>
    </form>

    @if ($admins->isEmpty())
        <x-empty-state title="No administrators match" icon="heroicon-o-magnifying-glass">
            Try a different name or email.
        </x-empty-state>
    @else
        <x-card :padding="false" class="overflow-hidden">
            <ul class="divide-y divide-line">
                @foreach ($admins as $admin)
                    <li class="flex items-center justify-between gap-4 px-5 py-4">
                        <div class="min-w-0">
                            <p class="font-semibold text-ink">
                                {{ $admin->name }}
                                @if ($admin->is(auth()->user()))
                                    <span class="text-sm font-normal text-ink-muted">(you)</span>
                                @endif
                            </p>
                            <p class="truncate text-sm text-ink-muted">{{ $admin->email }}</p>
                        </div>
                        <p class="shrink-0 text-sm text-ink-muted">Added {{ $admin->created_at->format('j M Y') }}</p>
                    </li>
                @endforeach
            </ul>
        </x-card>

        <div class="mt-6">{{ $admins->links() }}</div>
    @endif
</x-app-layout>
