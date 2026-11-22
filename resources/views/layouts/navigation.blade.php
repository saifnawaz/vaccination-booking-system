@php
    $user = auth()->user();
    $items = \App\Support\Navigation::for($user);
    $displayName = $user->isHospital() ? ($user->hospital?->name ?? $user->name) : $user->name;
@endphp

{{-- Forest header: white text at 80% (8.4:1) and 100% (12.2:1); see scripts/check-contrast.php. --}}
<header x-data="{ menuOpen: false }" class="sticky top-0 z-40 bg-forest text-white shadow-sm">
    <div class="mx-auto flex h-16 max-w-6xl items-center gap-4 px-4 sm:px-6 lg:px-8">
        <a href="{{ route('dashboard') }}" class="flex shrink-0 items-center gap-2.5 rounded-lg focus-visible:outline-white">
            <x-logo size="32" />
            <span class="text-lg font-bold tracking-tight text-white">{{ config('app.name') }}</span>
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
                               'relative flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium transition-colors duration-150 focus-visible:outline-white',
                               'bg-forest-soft text-white after:absolute after:inset-x-3 after:-bottom-[13px] after:h-0.5 after:rounded-full after:bg-primary-500' => $active,
                               'text-white/80 hover:bg-white/10 hover:text-white' => ! $active,
                           ])>
                            <x-dynamic-component :component="$item['icon']" class="size-5" aria-hidden="true" />
                            {{ $item['label'] }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </nav>

        <div class="ms-auto flex items-center gap-1 sm:gap-2 [&_button]:focus-visible:outline-white">
            <x-notification-bell />

            {{-- Account menu (all screen sizes) --}}
            <x-dropdown align="right" width="56">
                <x-slot name="trigger">
                    <button type="button" class="flex items-center gap-2 rounded-lg px-2 py-1.5 text-sm font-medium text-white transition-colors duration-150 hover:bg-white/10">
                        <span class="flex size-8 items-center justify-center rounded-full bg-primary-100 font-semibold text-primary-800" aria-hidden="true">
                            {{ mb_strtoupper(mb_substr($displayName, 0, 1)) }}
                        </span>
                        <span class="hidden max-w-40 truncate lg:inline">{{ $displayName }}</span>
                        <x-heroicon-o-chevron-down class="size-4 text-white/80" aria-hidden="true" />
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
                        class="rounded-lg p-2 text-white/80 hover:bg-white/10 hover:text-white md:hidden">
                    <x-heroicon-o-bars-3 class="size-6" x-show="! menuOpen" aria-hidden="true" />
                    <x-heroicon-o-x-mark class="size-6" x-show="menuOpen" x-cloak aria-hidden="true" />
                    <span class="sr-only">Toggle menu</span>
                </button>
            @endunless
        </div>
    </div>

    @unless ($user->isParent())
        <nav id="mobile-menu" aria-label="Main" x-show="menuOpen" x-cloak
             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="border-t border-white/10 md:hidden">
            <ul class="space-y-1 px-4 py-3">
                @foreach ($items as $item)
                    @php($active = request()->routeIs($item['active']))
                    <li>
                        <a href="{{ route($item['route']) }}"
                           @if ($active) aria-current="page" @endif
                           @class([
                               'flex items-center gap-3 rounded-lg px-3 py-2.5 text-base font-medium focus-visible:outline-white',
                               'bg-forest-soft text-white' => $active,
                               'text-white/80 hover:bg-white/10 hover:text-white' => ! $active,
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
    <nav aria-label="Main" class="fixed inset-x-0 bottom-0 z-40 border-t border-line bg-surface pb-[env(safe-area-inset-bottom)] shadow-[0_-1px_8px_rgb(15_61_46/0.06)] md:hidden">
        <ul class="mx-auto flex max-w-md">
            @foreach ($items as $item)
                @php($active = request()->routeIs($item['active']))
                <li class="flex-1">
                    <a href="{{ route($item['route']) }}"
                       @if ($active) aria-current="page" @endif
                       @class([
                           'relative flex flex-col items-center gap-1 py-2.5 text-xs font-medium transition-colors duration-150',
                           'text-primary-700 before:absolute before:inset-x-4 before:top-0 before:h-0.5 before:rounded-full before:bg-primary-600' => $active,
                           'text-ink-muted hover:text-ink' => ! $active,
                       ])>
                        <x-dynamic-component :component="$item['icon']" class="size-6" aria-hidden="true" />
                        {{ $item['label'] }}
                    </a>
                </li>
            @endforeach
        </ul>
    </nav>
@endif
