<x-guest-layout title="Choose a new password">
    <h1 class="text-2xl font-bold tracking-tight text-ink">Choose a new password</h1>

    <form method="POST" action="{{ route('password.store') }}" class="mt-6 space-y-5">
        @csrf

        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <x-field name="email" label="Email" type="email" :value="$request->email" required autofocus autocomplete="username" />

        <x-field name="password" label="New password" type="password" required autocomplete="new-password"
                 hint="At least 8 characters." />

        <x-field name="password_confirmation" label="Confirm new password" type="password" required autocomplete="new-password" />

        <x-button class="w-full" size="lg">Reset password</x-button>
    </form>
</x-guest-layout>
