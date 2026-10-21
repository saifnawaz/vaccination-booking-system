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
            'mx-auto max-w-6xl px-4 py-6 sm:px-6 sm:py-8 lg:px-8',
            'pb-28 md:pb-8' => auth()->user()?->isParent(),
        ])>
            <x-flash />

            {{ $header ?? '' }}

            {{ $slot }}
        </main>
    </body>
</html>
