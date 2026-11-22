<x-guest-layout title="Confirm your password">
    <h1 class="text-2xl font-bold tracking-tight text-ink">Confirm your password</h1>
    <p class="mt-1.5 text-ink-muted">This is a secure area. Please confirm your password to continue.</p>

    <form method="POST" action="{{ route('password.confirm') }}" class="mt-6 space-y-5">
        @csrf

        <x-field name="password" label="Password" type="password" required autofocus autocomplete="current-password" />

        <x-button class="w-full" size="lg">Confirm</x-button>
    </form>
</x-guest-layout>
