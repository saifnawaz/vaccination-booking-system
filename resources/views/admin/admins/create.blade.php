<x-app-layout title="Add administrator">
    <x-page-header title="Add administrator"
                   description="Administrators can approve and deactivate hospitals and see every report. Only add people you trust.">
        <x-slot:breadcrumb>
            <a href="{{ route('admin.admins.index') }}" class="inline-flex items-center gap-1 font-medium text-primary-700 hover:underline">
                <x-heroicon-o-chevron-left class="size-4" aria-hidden="true" /> Administrators
            </a>
        </x-slot:breadcrumb>
    </x-page-header>

    <x-card class="max-w-xl">
        <form method="POST" action="{{ route('admin.admins.store') }}" class="space-y-5">
            @csrf

            <x-field name="name" label="Full name" required autofocus autocomplete="off" />
            <x-field name="email" label="Email" type="email" required autocomplete="off" />
            <x-field name="password" label="Temporary password" type="password" required autocomplete="new-password"
                     hint="At least 8 characters. Share it securely and ask them to change it after logging in." />
            <x-field name="password_confirmation" label="Confirm temporary password" type="password" required autocomplete="new-password" />

            <div class="flex gap-3">
                <x-button>Create administrator</x-button>
                <x-button :href="route('admin.admins.index')" variant="ghost">Cancel</x-button>
            </div>
        </form>
    </x-card>
</x-app-layout>
