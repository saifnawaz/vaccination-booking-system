<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('layouts.partials.head', ['title' => $title])
    </head>
    <body class="min-h-screen font-sans">
        <div class="flex min-h-screen flex-col items-center px-4 py-8 sm:justify-center sm:py-12">
            <a href="{{ route('home') }}" class="flex items-center gap-3 rounded-lg">
                <x-application-logo class="size-11" />
                <span class="text-lg font-bold text-ink">{{ config('app.name') }}</span>
            </a>

            <main id="main" @class([
                'mt-8 w-full rounded-2xl bg-surface px-5 py-6 shadow-sm ring-1 ring-line sm:px-8 sm:py-8',
                'sm:max-w-md' => ! $wide,
                'sm:max-w-2xl' => $wide,
            ])>
                {{ $slot }}
            </main>
        </div>
    </body>
</html>
