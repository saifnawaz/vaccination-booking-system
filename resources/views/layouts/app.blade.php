<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('layouts.partials.head', ['title' => $title])
    </head>
    <body class="min-h-screen font-sans">
        <a href="#main" class="sr-only z-50 rounded-lg bg-primary-700 px-4 py-2 font-semibold text-white focus:not-sr-only focus:fixed focus:top-3 focus:left-3">
            Skip to main content
        </a>

        @include('layouts.navigation')

        {{-- Extra bottom padding on phones for parents, so the tab bar never covers content. --}}
        <main id="main" @class([
            'animate-fade-in',
            'mx-auto max-w-6xl px-4 py-6 sm:px-6 sm:py-8 lg:px-8',
            'pb-28 md:pb-8' => auth()->user()?->isParent(),
        ])>
            <x-flash />

            {{ $header ?? '' }}

            {{ $slot }}
        </main>

        <footer @class(['border-t border-line', 'pb-24 md:pb-0' => auth()->user()?->isParent()])>
            <div class="mx-auto flex max-w-6xl flex-col gap-1 px-4 py-6 text-xs text-ink-muted sm:flex-row sm:items-center sm:justify-between sm:px-6 lg:px-8">
                <span class="flex items-center gap-2"><x-logo size="16" /> {{ config('app.name') }} · {{ config('app.tagline') }}</span>
                <span>
                    <x-heroicon-o-exclamation-triangle class="-mt-0.5 inline size-4 text-warning-800" aria-hidden="true" />
                    The vaccination schedule is demo data and must be verified against the official EPI Pakistan schedule.
                </span>
            </div>
        </footer>
    </body>
</html>
