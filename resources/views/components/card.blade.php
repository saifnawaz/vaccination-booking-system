@props(['padding' => true])

<div {{ $attributes->merge(['class' => 'rounded-2xl bg-surface shadow-sm ring-1 ring-line'.($padding ? ' p-5 sm:p-6' : '')]) }}>
    {{ $slot }}
</div>
