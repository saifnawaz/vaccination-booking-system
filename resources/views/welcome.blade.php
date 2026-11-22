<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('layouts.partials.head', ['title' => null])
    </head>
    <body class="min-h-screen font-sans">
        <header class="mx-auto flex h-16 max-w-6xl items-center justify-between px-4 sm:px-6 lg:px-8">
            <a href="{{ route('home') }}" class="flex items-center gap-2.5 rounded-lg">
                <x-logo size="36" />
                <span class="text-xl font-bold tracking-tight text-forest">{{ config('app.name') }}</span>
            </a>

            <nav aria-label="Account" class="flex items-center gap-2">
                @auth
                    <x-button :href="route('dashboard')" size="sm">Go to dashboard</x-button>
                @else
                    <x-button :href="route('login')" variant="ghost" size="sm">Log in</x-button>
                    <x-button :href="route('register')" size="sm" class="hidden sm:inline-flex">Sign up</x-button>
                @endauth
            </nav>
        </header>

        <main id="main">
            <section class="mx-auto max-w-6xl px-4 pt-10 pb-16 sm:px-6 sm:pt-16 lg:px-8">
                <div class="max-w-2xl">
                    <x-badge tone="primary" icon="heroicon-o-heart">For parents and hospitals</x-badge>
                    <h1 class="mt-5 animate-fade-up text-4xl font-bold tracking-tight text-forest sm:text-6xl">
                        {{ config('app.tagline') }}
                    </h1>
                    <p class="mt-5 text-lg text-ink-muted">
                        Add your child once and we'll build their full vaccination schedule from their date of birth.
                        See what's due, book a slot at a hospital that has the vaccine in stock, and keep a
                        certificate of every dose.
                    </p>

                    @guest
                        <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                            <x-button :href="route('register')" size="lg">Create a parent account</x-button>
                            <x-button :href="route('register.hospital')" variant="secondary" size="lg">Register your hospital</x-button>
                        </div>
                    @endguest
                </div>
            </section>

            <section aria-labelledby="how-it-works" class="border-t border-line bg-card">
                <div class="mx-auto max-w-6xl px-4 py-14 sm:px-6 lg:px-8">
                    <h2 id="how-it-works" class="text-2xl font-bold tracking-tight text-ink">How it works</h2>

                    <ol class="mt-8 grid gap-6 sm:grid-cols-3">
                        @foreach ([
                            ['heroicon-o-user-plus', 'Add your child', 'Enter a date of birth and get every dose of the national schedule, with what is due, upcoming or overdue.'],
                            ['heroicon-o-calendar-days', 'Book a slot', 'Choose a nearby hospital that has the vaccine in stock and pick a time that suits you.'],
                            ['heroicon-o-document-text', 'Keep a record', 'Each completed dose is added to a printable vaccination certificate.'],
                        ] as $i => [$icon, $title, $text])
                            <li class="animate-fade-up rounded-2xl bg-surface p-6 shadow-sm ring-1 ring-line">
                                <span class="flex size-11 items-center justify-center rounded-xl bg-primary-700 text-white">
                                    <x-dynamic-component :component="$icon" class="size-6" aria-hidden="true" />
                                </span>
                                <h3 class="mt-4 font-semibold text-ink">
                                    <span class="sr-only">Step {{ $i + 1 }}:</span> {{ $title }}
                                </h3>
                                <p class="mt-2 text-sm text-ink-muted">{{ $text }}</p>
                            </li>
                        @endforeach
                    </ol>
                </div>
            </section>
        </main>

        <footer class="border-t border-line">
            <div class="mx-auto max-w-6xl px-4 py-8 text-sm text-ink-muted sm:px-6 lg:px-8">
                {{ config('app.name') }} · {{ config('app.tagline') }} Always follow the advice of your doctor or vaccination centre.
                <span class="mt-1 block">Demo app: the vaccination schedule is demonstration data and must be verified against the official EPI Pakistan schedule.</span>
            </div>
        </footer>
    </body>
</html>
