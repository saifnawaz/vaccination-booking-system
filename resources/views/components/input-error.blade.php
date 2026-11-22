@props(['messages'])

@if ($messages)
    <ul {{ $attributes->merge(['class' => 'mt-1.5 space-y-1 text-sm text-danger-700']) }}>
        @foreach ((array) $messages as $message)
            <li class="flex items-start gap-1.5">
                <x-heroicon-o-exclamation-triangle class="mt-0.5 size-4 shrink-0" aria-hidden="true" />
                <span>{{ $message }}</span>
            </li>
        @endforeach
    </ul>
@endif
