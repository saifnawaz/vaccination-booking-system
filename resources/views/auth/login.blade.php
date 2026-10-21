<x-guest-layout title="Log in">
    <h1 class="text-2xl font-bold tracking-tight text-ink">Welcome back</h1>
    <p class="mt-1.5 text-ink-muted">Log in to manage vaccinations and appointments.</p>

    <x-auth-session-status class="mt-6" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-5">
        @csrf

        <x-field name="email" label="Email" type="email" required autofocus autocomplete="username" />

        <x-field name="password" label="Password" type="password" required autocomplete="current-password" />

        <div class="flex items-center justify-between gap-4">
            <label for="remember_me" class="inline-flex items-center gap-2 text-sm text-ink-muted">
                <input id="remember_me" type="checkbox" name="remember"
                       class="size-4 rounded border-line-input text-primary-700 focus:ring-primary-600">
                Remember me
            </label>

            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="text-sm font-medium text-primary-700 underline-offset-2 hover:underline">
                    Forgot your password?
                </a>
            @endif
        </div>

        <x-button class="w-full" size="lg">Log in</x-button>
    </form>

    <div class="mt-8 space-y-2 border-t border-line pt-6 text-center text-sm text-ink-muted">
        <p>
            New here?
            <a href="{{ route('register') }}" class="font-semibold text-primary-700 underline-offset-2 hover:underline">Create a parent account</a>
        </p>
        <p>
            Run a vaccination centre?
            <a href="{{ route('register.hospital') }}" class="font-semibold text-primary-700 underline-offset-2 hover:underline">Register your hospital</a>
        </p>
    </div>
</x-guest-layout>
