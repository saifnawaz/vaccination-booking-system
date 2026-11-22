<section aria-labelledby="profile-information-heading">
    <header>
        <h2 id="profile-information-heading" class="text-lg font-semibold text-ink">Profile information</h2>
        <p class="mt-1 text-sm text-ink-muted">Your name, email address and phone number.</p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    @if (session('status') === 'profile-updated')
        <x-alert type="success" class="mt-5">Your profile has been saved.</x-alert>
    @endif

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-5">
        @csrf
        @method('patch')

        <x-field name="name" label="Full name" :value="$user->name" required autocomplete="name" />

        <div>
            <x-field name="email" label="Email" type="email" :value="$user->email" required autocomplete="username" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <p class="mt-2 text-sm text-ink">
                    Your email address is unverified.
                    <button form="send-verification" class="font-semibold text-primary-700 underline-offset-2 hover:underline">
                        Re-send the verification email
                    </button>
                </p>

                @if (session('status') === 'verification-link-sent')
                    <p class="mt-2 text-sm font-medium text-success-700">A new verification link has been sent to your email address.</p>
                @endif
            @endif
        </div>

        <x-field name="phone" label="Mobile number" type="tel" :value="$user->phone" autocomplete="tel" inputmode="tel" />

        <x-button>Save</x-button>
    </form>
</section>
