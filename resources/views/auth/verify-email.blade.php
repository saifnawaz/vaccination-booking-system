<x-guest-layout title="Verify your email">
    <h1 class="text-2xl font-bold tracking-tight text-ink">Verify your email</h1>
    <p class="mt-1.5 text-ink-muted">
        Thanks for signing up. Please confirm your email address with the link we just sent you.
        If it hasn't arrived, we can send another.
    </p>

    @if (session('status') == 'verification-link-sent')
        <x-alert type="success" class="mt-6">A new verification link has been sent to your email address.</x-alert>
    @endif

    <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <x-button>Resend verification email</x-button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <x-button variant="ghost">Log out</x-button>
        </form>
    </div>
</x-guest-layout>
