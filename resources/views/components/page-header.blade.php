@props(['title', 'description' => null])

<div {{ $attributes->merge(['class' => 'mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between']) }}>
    <div class="min-w-0">
        @isset($breadcrumb)
            <div class="mb-2 text-sm">{{ $breadcrumb }}</div>
        @endisset
        <h1 class="text-2xl font-bold tracking-tight text-ink sm:text-3xl">{{ $title }}</h1>
        @if ($description)
            <p class="mt-1.5 text-ink-muted">{{ $description }}</p>
        @endif
    </div>
    @isset($actions)
        <div class="flex shrink-0 flex-wrap gap-3">{{ $actions }}</div>
    @endisset
</div>
