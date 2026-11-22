<x-guest-layout title="Registration received">
    <div class="text-center">
        <span class="mx-auto flex size-14 items-center justify-center rounded-full bg-success-50 text-success-700">
            <x-heroicon-o-check-circle class="size-8" aria-hidden="true" />
        </span>

        <h1 class="mt-5 text-2xl font-bold tracking-tight text-ink">Registration received</h1>

        <p class="mt-3 text-ink-muted">
            Thank you. An administrator will review your hospital's details.
            Once it has been approved you can log in with the email and password you just chose.
        </p>

        <ol class="mt-8 space-y-3 text-start text-sm">
            <li class="flex gap-3">
                <x-badge tone="success" icon="heroicon-o-check-circle">Done</x-badge>
                <span class="text-ink">Registration submitted</span>
            </li>
            <li class="flex gap-3">
                <x-badge tone="warning" icon="heroicon-o-clock">Next</x-badge>
                <span class="text-ink">An administrator approves your hospital</span>
            </li>
            <li class="flex gap-3">
                <x-badge tone="neutral" icon="heroicon-o-calendar-days">Then</x-badge>
                <span class="text-ink">Log in, add your vaccine stock and open appointment slots</span>
            </li>
        </ol>

        <x-button :href="route('login')" variant="secondary" class="mt-8 w-full">Go to log in</x-button>
    </div>
</x-guest-layout>
