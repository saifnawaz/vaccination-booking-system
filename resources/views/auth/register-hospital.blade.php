<x-guest-layout title="Register your hospital" wide>
    <h1 class="text-2xl font-bold tracking-tight text-ink">Register your hospital</h1>
    <p class="mt-1.5 text-ink-muted">Offer vaccination appointments to parents in your area.</p>

    <x-alert type="info" class="mt-6">
        An administrator reviews every new hospital. You'll be able to log in as soon as yours is approved.
    </x-alert>

    <form method="POST" action="{{ route('register.hospital') }}" class="mt-8 space-y-8">
        @csrf

        <fieldset class="space-y-5">
            <legend class="text-base font-semibold text-ink">Hospital details</legend>

            <x-field name="hospital_name" label="Hospital name" required autofocus autocomplete="organization" />

            <x-field name="address" label="Street address" required autocomplete="street-address" />

            <div class="grid gap-5 sm:grid-cols-2">
                <x-field name="city" label="City" required autocomplete="address-level2" />
                <x-field name="hospital_phone" label="Hospital phone" type="tel" required inputmode="tel" />
            </div>

            <x-field name="license_number" label="Licence or registration number"
                     hint="Optional, but it helps the administrator approve you faster." />
        </fieldset>

        <fieldset class="space-y-5 border-t border-line pt-8">
            <legend class="text-base font-semibold text-ink">Your login</legend>
            <p class="-mt-2 text-sm text-ink-muted">The person who will manage appointments and stock for this hospital.</p>

            <x-field name="name" label="Contact person" required autocomplete="name" />

            <x-field name="email" label="Work email" type="email" required autocomplete="username" />

            <div class="grid gap-5 sm:grid-cols-2">
                <x-field name="password" label="Password" type="password" required autocomplete="new-password"
                         hint="At least 8 characters." />
                <x-field name="password_confirmation" label="Confirm password" type="password" required autocomplete="new-password" />
            </div>
        </fieldset>

        <x-button class="w-full" size="lg">Submit for approval</x-button>
    </form>

    <p class="mt-8 border-t border-line pt-6 text-center text-sm text-ink-muted">
        Already registered?
        <a href="{{ route('login') }}" class="font-semibold text-primary-700 underline-offset-2 hover:underline">Log in</a>
    </p>
</x-guest-layout>
