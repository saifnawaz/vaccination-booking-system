<section aria-labelledby="delete-account-heading">
    <header>
        <h2 id="delete-account-heading" class="text-lg font-semibold text-ink">Close account</h2>
        <p class="mt-1 text-sm text-ink-muted">
            Closing your account signs you out and removes your access. Your children's vaccination records
            are kept so that hospitals' records stay complete.
        </p>
    </header>

    <x-button type="button" variant="danger" class="mt-5"
              x-data="" x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')">
        Close account
    </x-button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" labelledby="confirm-user-deletion-title" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
            @csrf
            @method('delete')

            <h2 id="confirm-user-deletion-title" class="text-lg font-semibold text-ink">Close your account?</h2>
            <p class="mt-2 text-sm text-ink-muted">Enter your password to confirm.</p>

            <div class="mt-5">
                <x-field name="password" id="delete_account_password" label="Password" type="password"
                         error-bag="userDeletion" autocomplete="current-password" />
            </div>

            <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <x-button variant="secondary" type="button" x-on:click="$dispatch('close')">Cancel</x-button>
                <x-button variant="danger">Close account</x-button>
            </div>
        </form>
    </x-modal>
</section>
