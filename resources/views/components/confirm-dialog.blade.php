{{--
    Confirmation dialog for destructive or important actions. The action only happens
    when the user presses the confirm button inside the dialog.

    <x-button type="button" variant="danger" x-on:click="$dispatch('open-modal', 'deactivate-3')">Deactivate</x-button>
    <x-confirm-dialog name="deactivate-3" title="Deactivate hospital?" :action="route(…)" method="PATCH" confirm-label="Deactivate">
        Explanation of what will happen.
        <x-slot:fields><input type="hidden" name="status" value="inactive"></x-slot:fields>
    </x-confirm-dialog>
--}}
@props([
    'name',
    'title',
    'action',
    'method' => 'POST',
    'confirmLabel' => 'Confirm',
    'variant' => 'danger',
])

<x-modal :name="$name" max-width="md" :labelledby="$name.'-title'" focusable>
    <form method="POST" action="{{ $action }}" class="p-6">
        @csrf
        @unless (strtoupper($method) === 'POST')
            @method($method)
        @endunless

        <div class="flex items-start gap-4">
            <span @class([
                'flex size-10 shrink-0 items-center justify-center rounded-full',
                'bg-danger-50 text-danger-700' => $variant === 'danger',
                'bg-primary-50 text-primary-700' => $variant !== 'danger',
            ])>
                <x-heroicon-o-exclamation-triangle class="size-5" aria-hidden="true" />
            </span>
            <div class="min-w-0">
                <h2 id="{{ $name }}-title" class="text-lg font-semibold text-ink">{{ $title }}</h2>
                <div class="mt-2 text-sm text-ink-muted">{{ $slot }}</div>
            </div>
        </div>

        {{ $fields ?? '' }}

        <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
            <x-button variant="secondary" type="button" x-on:click="$dispatch('close')">Cancel</x-button>
            <x-button :variant="$variant">{{ $confirmLabel }}</x-button>
        </div>
    </form>
</x-modal>
