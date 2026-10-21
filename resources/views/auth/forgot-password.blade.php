<x-guest-layout title="Reset your password">
    <h1 class="text-2xl font-bold tracking-tight text-ink">Reset your password</h1>
    <p class="mt-1.5 text-ink-muted">Enter your email address and we'll send you a link to choose a new password.</p>

    <x-auth-session-status class="mt-6" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="mt-6 space-y-5">
        @csrf

        <x-field name="email" label="Email" type="email" required autofocus autocomplete="username" />

        <x-button class="w-full" size="lg">Email me a reset link</x-button>
    </form>

    <p class="mt-8 border-t border-line pt-6 text-center text-sm text-ink-muted">
        Remembered it?
        <a href="{{ route('login') }}" class="font-semibold text-primary-700 underline-offset-2 hover:underline">Back to log in</a>
    </p>
</x-guest-layout>
