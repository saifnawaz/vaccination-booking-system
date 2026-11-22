<x-guest-layout title="Create a parent account">
    <h1 class="text-2xl font-bold tracking-tight text-ink">Create a parent account</h1>
    <p class="mt-1.5 text-ink-muted">Keep every child's vaccinations on schedule, and book appointments at hospitals near you.</p>

    <form method="POST" action="{{ route('register') }}" class="mt-6 space-y-5">
        @csrf

        <x-field name="name" label="Full name" required autofocus autocomplete="name" />

        <x-field name="email" label="Email" type="email" required autocomplete="username" />

        <x-field name="phone" label="Mobile number" type="tel" autocomplete="tel" inputmode="tel"
                 hint="Optional. Hospitals may use it to contact you about an appointment." />

        <x-field name="password" label="Password" type="password" required autocomplete="new-password"
                 hint="At least 8 characters." />

        <x-field name="password_confirmation" label="Confirm password" type="password" required autocomplete="new-password" />

        <x-button class="w-full" size="lg">Create account</x-button>
    </form>

    <div class="mt-8 space-y-2 border-t border-line pt-6 text-center text-sm text-ink-muted">
        <p>
            Already have an account?
            <a href="{{ route('login') }}" class="font-semibold text-primary-700 underline-offset-2 hover:underline">Log in</a>
        </p>
        <p>
            Registering a hospital instead?
            <a href="{{ route('register.hospital') }}" class="font-semibold text-primary-700 underline-offset-2 hover:underline">Use the hospital form</a>
        </p>
    </div>
</x-guest-layout>
