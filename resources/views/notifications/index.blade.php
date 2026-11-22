<x-app-layout title="Notifications">
    <x-page-header title="Notifications" :description="$unread ? $unread.' unread' : 'You\'re all caught up.'">
        @if ($unread)
            <x-slot:actions>
                <form method="POST" action="{{ route('notifications.read-all') }}">
                    @csrf
                    <x-button variant="secondary">Mark all as read</x-button>
                </form>
            </x-slot:actions>
        @endif
    </x-page-header>

    <nav aria-label="Filter notifications" class="mb-4 flex gap-2">
        @foreach (['all' => 'All', 'unread' => 'Unread'] as $value => $label)
            <a href="{{ route('notifications.index', $value === 'all' ? [] : ['show' => $value]) }}" data-filter-link
               @if ($filter === $value) aria-current="page" @endif
               @class([
                   'rounded-full px-4 py-1.5 text-sm font-semibold transition-colors duration-200 ease-out',
                   'bg-primary-700 text-white' => $filter === $value,
                   'bg-surface text-ink ring-1 ring-line-input hover:bg-surface-muted' => $filter !== $value,
               ])>{{ $label }}</a>
        @endforeach
    </nav>

    <x-list-region>
    @if ($notifications->isEmpty())
        <x-empty-state :title="$filter === 'unread' ? 'No unread notifications' : 'No notifications yet'" icon="heroicon-o-bell">
            Booking updates and vaccination reminders will appear here.
        </x-empty-state>
    @else
        <x-card :padding="false" class="overflow-hidden">
            <ul class="divide-y divide-line">
                @foreach ($notifications as $notification)
                    <li @class(['flex gap-4 px-5 py-4', 'bg-primary-50/50' => ! $notification->read_at])>
                        @include('notifications.partials.icon', ['kind' => $notification->data['kind'] ?? 'info'])
                        <div class="min-w-0 flex-1">
                            <a href="{{ route('notifications.open', $notification->id) }}" class="font-semibold text-ink hover:text-primary-700 hover:underline">
                                {{ $notification->data['title'] ?? 'Notification' }}
                            </a>
                            @unless ($notification->read_at)
                                <x-badge tone="primary" class="ms-2">New</x-badge>
                            @endunless
                            <p class="mt-0.5 text-sm text-ink">{{ $notification->data['body'] ?? '' }}</p>
                            <p class="mt-1 text-xs text-ink-subtle">{{ $notification->created_at->format('j M Y, g:i a') }} · {{ $notification->created_at->diffForHumans() }}</p>
                        </div>
                        @unless ($notification->read_at)
                            <form method="POST" action="{{ route('notifications.read', $notification->id) }}" class="shrink-0">
                                @csrf
                                <x-button variant="ghost" size="sm">Mark read</x-button>
                            </form>
                        @endunless
                    </li>
                @endforeach
            </ul>
        </x-card>

        <div class="mt-6">{{ $notifications->links() }}</div>
    @endif
    </x-list-region>
</x-app-layout>
