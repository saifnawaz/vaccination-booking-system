<section aria-labelledby="update-password-heading">
    <header>
        <h2 id="update-password-heading" class="text-lg font-semibold text-ink">Change password</h2>
        <p class="mt-1 text-sm text-ink-muted">Use a long password that you don't use anywhere else.</p>
    </header>

    @if (session('status') === 'password-updated')
        <x-alert type="success" class="mt-5">Your password has been changed.</x-alert>
    @endif

    <form method="post" action="{{ route('password.update') }}" class="mt-6 space-y-5">
        @csrf
        @method('put')

        <x-field name="current_password" id="update_password_current_password" label="Current password" type="password"
                 error-bag="updatePassword" autocomplete="current-password" />

        <x-field name="password" id="update_password_password" label="New password" type="password"
                 error-bag="updatePassword" autocomplete="new-password" hint="At least 8 characters." />

        <x-field name="password_confirmation" id="update_password_password_confirmation" label="Confirm new password" type="password"
                 error-bag="updatePassword" autocomplete="new-password" />

        <x-button>Change password</x-button>
    </form>
</section>
