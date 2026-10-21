{{-- Kept for Breeze's views; prefer <x-button variant="secondary"> in new code. --}}
<x-button variant="secondary" {{ $attributes->merge(['type' => 'button']) }}>{{ $slot }}</x-button>
