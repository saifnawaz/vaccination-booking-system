<x-app-layout title="Hospital profile">
    <x-page-header title="Hospital profile"
                   description="These details are shown to parents when they choose where to book." />

    <x-card class="max-w-2xl">
        <form method="POST" action="{{ route('hospital.profile.update') }}" class="space-y-5">
            @csrf
            @method('PUT')

            <x-field name="hospital_name" label="Hospital name" :value="$hospital->name" required autocomplete="organization" />
            <x-field name="address" label="Street address" :value="$hospital->address" required autocomplete="street-address" />

            <div class="grid gap-5 sm:grid-cols-2">
                <x-field name="city" label="City" :value="$hospital->city" required autocomplete="address-level2" />
                <x-field name="hospital_phone" label="Hospital phone" type="tel" :value="$hospital->phone" required inputmode="tel" />
            </div>

            <x-field name="license_number" label="Licence or registration number" :value="$hospital->license_number" />

            <x-button>Save changes</x-button>
        </form>
    </x-card>

    <p class="mt-6 max-w-2xl text-sm text-ink-muted">
        To change your login email or password, go to
        <a href="{{ route('profile.edit') }}" class="font-semibold text-primary-700 hover:underline">account settings</a>.
    </p>
</x-app-layout>
