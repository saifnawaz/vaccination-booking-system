<x-app-layout title="Account settings">
    <x-page-header title="Account settings" description="Your login details and password." />

    <div class="max-w-2xl space-y-6">
        <x-card>
            @include('profile.partials.update-profile-information-form')
        </x-card>

        <x-card>
            @include('profile.partials.update-password-form')
        </x-card>

        @can('delete', $user)
            <x-card>
                @include('profile.partials.delete-user-form')
            </x-card>
        @endcan
    </div>
</x-app-layout>
