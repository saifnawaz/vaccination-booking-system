@php
    $user = auth()->user();
    $items = \App\Support\Navigation::for($user);
    $displayName = $user->isHospital() ? ($user->hospital?->name ?? $user->name) : $user->name;
@endphp

<header x-data="{ menuOpen: false }" class="sticky top-0 z-40 border-b border-line bg-surface/95 backdrop-blur">
    <div class="mx-auto flex h-16 max-w-6xl items-center gap-4 px-4 sm:px-6 lg:px-8">
        <a href="{{ route('dashboard') }}" class="flex shrink-0 items-center gap-2.5 rounded-lg">
            <x-application-logo class="size-9" />
            <span class="hidden font-bold text-ink sm:inline">{{ config('app.name') }}</span>
        </a>

        {{-- Desktop menu --}}
        <nav aria-label="Main" class="hidden flex-1 md:block">
            <ul class="flex items-center gap-1">
                @foreach ($items as $item)
                    @php($active = request()->routeIs($item['active']))
                    <li>
                        <a href="{{ route($item['route']) }}"
                           @if ($active) aria-current="page" @endif
                           @class([
                               'flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium transition-colors',
                               'bg-primary-50 text-primary-800' => $active,
                               'text-ink-muted hover:bg-surface-muted hover:text-ink' => ! $active,
                           ])>
                            <x-dynamic-component :component="$item['icon']" class="size-5" aria-hidden="true" />
                            {{ $item['label'] }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </nav>

        <div class="ms-auto flex items-center gap-2">
            {{-- Account menu (all screen sizes) --}}
            <x-dropdown align="right" width="56">
                <x-slot name="trigger">
                    <button type="button" class="flex items-center gap-2 rounded-lg px-2 py-1.5 text-sm font-medium text-ink hover:bg-surface-muted">
                        <span class="flex size-8 items-center justify-center rounded-full bg-primary-100 font-semibold text-primary-800" aria-hidden="true">
                            {{ mb_strtoupper(mb_substr($displayName, 0, 1)) }}
                        </span>
                        <span class="hidden max-w-40 truncate lg:inline">{{ $displayName }}</span>
                        <x-heroicon-o-chevron-down class="size-4 text-ink-subtle" aria-hidden="true" />
                        <span class="sr-only">Open account menu</span>
                    </button>
                </x-slot>

                <x-slot name="content">
                    <div class="border-b border-line px-4 py-3">
                        <p class="truncate text-sm font-semibold text-ink">{{ $displayName }}</p>
                        <p class="truncate text-sm text-ink-muted">{{ $user->email }}</p>
                        <x-badge tone="primary" class="mt-2">{{ $user->role->label() }}</x-badge>
                    </div>

                    <x-dropdown-link :href="route('profile.edit')">
                        <x-heroicon-o-user-circle class="size-5 text-ink-subtle" aria-hidden="true" />
                        Account settings
                    </x-dropdown-link>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="flex w-full items-center gap-2 px-4 py-2 text-start text-sm text-ink hover:bg-surface-muted focus:bg-surface-muted focus:outline-hidden">
                            <x-heroicon-o-arrow-right-start-on-rectangle class="size-5 text-ink-subtle" aria-hidden="true" />
                            Log out
                        </button>
                    </form>
                </x-slot>
            </x-dropdown>

            {{-- Hamburger: admin and hospital only. Parents get the bottom tab bar instead. --}}
            @unless ($user->isParent())
                <button type="button"
                        x-on:click="menuOpen = ! menuOpen"
                        x-bind:aria-expanded="menuOpen.toString()"
                        aria-controls="mobile-menu"
                        class="rounded-lg p-2 text-ink-muted hover:bg-surface-muted hover:text-ink md:hidden">
                    <x-heroicon-o-bars-3 class="size-6" x-show="! menuOpen" aria-hidden="true" />
                    <x-heroicon-o-x-mark class="size-6" x-show="menuOpen" x-cloak aria-hidden="true" />
                    <span class="sr-only">Toggle menu</span>
                </button>
            @endunless
        </div>
    </div>

    @unless ($user->isParent())
        <nav id="mobile-menu" aria-label="Main" x-show="menuOpen" x-cloak x-transition class="border-t border-line md:hidden">
            <ul class="space-y-1 px-4 py-3">
                @foreach ($items as $item)
                    @php($active = request()->routeIs($item['active']))
                    <li>
                        <a href="{{ route($item['route']) }}"
                           @if ($active) aria-current="page" @endif
                           @class([
                               'flex items-center gap-3 rounded-lg px-3 py-2.5 text-base font-medium',
                               'bg-primary-50 text-primary-800' => $active,
                               'text-ink hover:bg-surface-muted' => ! $active,
                           ])>
                            <x-dynamic-component :component="$item['icon']" class="size-5" aria-hidden="true" />
                            {{ $item['label'] }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </nav>
    @endunless
</header>

{{-- Parent bottom tab bar on phones: the main navigation pattern for the mobile-first side. --}}
@if ($user->isParent())
    <nav aria-label="Main" class="fixed inset-x-0 bottom-0 z-40 border-t border-line bg-surface pb-[env(safe-area-inset-bottom)] md:hidden">
        <ul class="mx-auto flex max-w-md">
            @foreach ($items as $item)
                @php($active = request()->routeIs($item['active']))
                <li class="flex-1">
                    <a href="{{ route($item['route']) }}"
                       @if ($active) aria-current="page" @endif
                       @class([
                           'flex flex-col items-center gap-1 py-2.5 text-xs font-medium',
                           'text-primary-700' => $active,
                           'text-ink-muted' => ! $active,
                       ])>
                        <x-dynamic-component :component="$item['icon']" class="size-6" aria-hidden="true" />
                        {{ $item['label'] }}
                    </a>
                </li>
            @endforeach
        </ul>
    </nav>
@endif
