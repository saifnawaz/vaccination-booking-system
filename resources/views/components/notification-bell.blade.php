{{-- Header bell: unread count and the latest notifications. --}}
@php
    $user = auth()->user();
    $unread = $user->unreadNotifications()->count();
    $latest = $user->notifications()->limit(5)->get();
@endphp

{{-- On phones the panel is pinned to the viewport (1rem from each edge) so it never runs off-screen. --}}
<x-dropdown align="right" width="w-96 max-sm:fixed max-sm:inset-x-4 max-sm:top-16 max-sm:w-auto">
    <x-slot name="trigger">
        <button type="button" class="relative rounded-lg p-2 text-white/80 transition-colors duration-150 hover:bg-white/10 hover:text-white"
                aria-label="Notifications{{ $unread ? ", {$unread} unread" : '' }}">
            <x-heroicon-o-bell class="size-6" aria-hidden="true" />
            @if ($unread)
                {{-- Mustard fill with forest text (5.4:1): an unread count is news, not an error, so not red. --}}
                <span class="absolute -top-0.5 -right-0.5 flex min-w-5 items-center justify-center rounded-full bg-accent-400 px-1 text-xs font-bold text-forest ring-2 ring-forest" aria-hidden="true">
                    {{ $unread > 9 ? '9+' : $unread }}
                </span>
            @endif
        </button>
    </x-slot>

    <x-slot name="content">
        <div class="flex items-center justify-between border-b border-line px-4 py-3">
            <p class="font-semibold text-ink">Notifications</p>
            @if ($unread)
                <form method="POST" action="{{ route('notifications.read-all') }}">
                    @csrf
                    <button type="submit" class="text-sm font-semibold text-primary-700 hover:underline">Mark all as read</button>
                </form>
            @endif
        </div>

        @if ($latest->isEmpty())
            <p class="px-4 py-6 text-center text-sm text-ink-muted">You're all caught up.</p>
        @else
            <ul class="max-h-96 divide-y divide-line overflow-y-auto">
                @foreach ($latest as $notification)
                    <li>
                        <a href="{{ route('notifications.open', $notification->id) }}"
                           class="flex gap-3 px-4 py-3 hover:bg-surface-muted {{ $notification->read_at ? '' : 'bg-primary-50/60' }}">
                            @include('notifications.partials.icon', ['kind' => $notification->data['kind'] ?? 'info'])
                            <span class="min-w-0">
                                <span class="block text-sm font-semibold text-ink">
                                    {{ $notification->data['title'] ?? 'Notification' }}
                                    @unless ($notification->read_at)<span class="sr-only">(unread)</span>@endunless
                                </span>
                                <span class="line-clamp-2 block text-sm text-ink-muted">{{ $notification->data['body'] ?? '' }}</span>
                                <span class="mt-0.5 block text-xs text-ink-subtle">{{ $notification->created_at->diffForHumans() }}</span>
                            </span>
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif

        <a href="{{ route('notifications.index') }}" class="block border-t border-line px-4 py-3 text-center text-sm font-semibold text-primary-700 hover:bg-surface-muted">
            See all notifications
        </a>
    </x-slot>
</x-dropdown>
