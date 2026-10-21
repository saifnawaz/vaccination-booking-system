@props(['title', 'icon' => 'heroicon-o-sparkles'])

<div {{ $attributes->merge(['class' => 'flex flex-col items-center rounded-2xl border border-dashed border-line-strong bg-surface px-6 py-10 text-center']) }}>
    <span class="flex size-12 items-center justify-center rounded-full bg-primary-50 text-primary-700">
        <x-dynamic-component :component="$icon" class="size-6" aria-hidden="true" />
    </span>
    <h3 class="mt-4 text-base font-semibold text-ink">{{ $title }}</h3>
    @if ($slot->isNotEmpty())
        <div class="mt-1.5 max-w-sm text-sm text-ink-muted">{{ $slot }}</div>
    @endif
    @isset($actions)
        <div class="mt-5 flex flex-wrap justify-center gap-3">{{ $actions }}</div>
    @endisset
</div>
