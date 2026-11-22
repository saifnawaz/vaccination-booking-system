<x-app-layout :title="'Edit '.$child->first_name">
    <x-page-header :title="'Edit '.$child->first_name">
        <x-slot:breadcrumb>
            <a href="{{ route('parent.children.show', $child) }}" class="inline-flex items-center gap-1 font-medium text-primary-700 hover:underline">
                <x-heroicon-o-chevron-left class="size-4" aria-hidden="true" /> {{ $child->first_name }}'s schedule
            </a>
        </x-slot:breadcrumb>
    </x-page-header>

    <x-card class="max-w-2xl">
        <form method="POST" action="{{ route('parent.children.update', $child) }}">
            @csrf
            @method('PUT')
            @include('parent.children.partials.form')

            <x-alert type="info" class="mt-5">
                If you correct the date of birth, every upcoming dose moves to match. Doses already given stay as they are.
            </x-alert>

            <div class="mt-6 flex flex-col gap-3 sm:flex-row">
                <x-button size="lg">Save changes</x-button>
                <x-button :href="route('parent.children.show', $child)" variant="ghost" size="lg">Cancel</x-button>
            </div>
        </form>
    </x-card>

    <x-card class="mt-6 max-w-2xl">
        <h2 class="text-base font-semibold text-ink">Remove {{ $child->first_name }}</h2>
        <p class="mt-1 text-sm text-ink-muted">
            {{ $child->first_name }} will no longer appear in your account. Vaccination records already kept by hospitals are not affected.
        </p>
        <x-button type="button" variant="danger" class="mt-4"
                  x-data x-on:click="$dispatch('open-modal', 'remove-child')">
            Remove {{ $child->first_name }}
        </x-button>
    </x-card>

    <x-confirm-dialog name="remove-child" :title="'Remove '.$child->first_name.'?'"
                      :action="route('parent.children.destroy', $child)" method="DELETE"
                      :confirm-label="'Remove '.$child->first_name">
        {{ $child->fullName() }} and their schedule will be removed from your account.
    </x-confirm-dialog>
</x-app-layout>
