@props(['padding' => true])

<div {{ $attributes->merge(['class' => 'animate-fade-up rounded-2xl bg-card shadow-sm ring-1 ring-line'.($padding ? ' p-5 sm:p-6' : '')]) }}>
    {{ $slot }}
</div>
