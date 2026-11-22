<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('layouts.partials.head', ['title' => $title])
    </head>
    <body class="min-h-screen bg-card font-sans">
        @php($intro = request()->routeIs('login', 'register', 'register.hospital'))

        <div class="flex min-h-screen flex-col items-center px-4 py-8 sm:justify-center sm:py-12">
            <a href="{{ route('home') }}" class="flex flex-col items-center gap-3 rounded-lg">
                {{-- On login/register the bars rotate into place, then the wordmark fades up. --}}
                <x-logo size="56" :animated="$intro" />
                <span @class(['text-center', 'tika-intro-word' => $intro])>
                    <span class="block text-2xl font-bold tracking-tight text-forest">{{ config('app.name') }}</span>
                    <span class="block text-sm text-ink-muted">{{ config('app.tagline') }}</span>
                </span>
            </a>

            <main id="main" @class([
                'mt-8 w-full animate-fade-up rounded-2xl bg-surface px-5 py-6 shadow-sm ring-1 ring-line sm:px-8 sm:py-8',
                'sm:max-w-md' => ! $wide,
                'sm:max-w-2xl' => $wide,
            ])>
                {{ $slot }}
            </main>

            <p class="mt-8 max-w-md text-center text-xs text-ink-muted">
                Demo app: the vaccination schedule is demonstration data and must be verified against the official EPI Pakistan schedule.
            </p>
        </div>
    </body>
</html>
