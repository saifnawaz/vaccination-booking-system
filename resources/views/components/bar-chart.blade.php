{{--
    Single-series bar chart in plain HTML/CSS (no JS library), following the dataviz rules:
    one hue (the chart token), thin marks with 4px rounded data-ends from one baseline,
    the value at each bar's end, a hover/focus tooltip per bar, and a table view.
    A single series needs no legend: the title says what is plotted.

    <x-bar-chart title="Doses given per month" :data="[['label' => 'Jan', 'value' => 12], …]" />
    orientation: "column" (vertical bars, e.g. over time) or "row" (horizontal, many/long labels)
--}}
@props([
    'title',
    'data',
    'orientation' => 'column',
    'unit' => null,
    'empty' => 'No data yet.',
    'description' => null,
])

@php
    $id = 'chart-'.\Illuminate\Support\Str::slug($title);
    $max = max(1, collect($data)->max('value'));
    // Round the scale up to a clean number so bars don't all touch the top.
    $step = $max <= 5 ? 1 : 10 ** (int) floor(log10($max));
    $scale = (int) (ceil($max / $step) * $step);
    $hasData = collect($data)->sum('value') > 0;
    $fmt = fn ($v) => number_format($v).($unit ? ' '.str($unit)->plural($v) : '');
@endphp

<figure {{ $attributes->merge(['class' => 'rounded-2xl bg-surface p-5 shadow-sm ring-1 ring-line']) }} aria-labelledby="{{ $id }}-title">
    <figcaption>
        <h3 id="{{ $id }}-title" class="text-base font-semibold text-ink">{{ $title }}</h3>
        @if ($description)
            <p class="mt-0.5 text-sm text-ink-muted">{{ $description }}</p>
        @endif
    </figcaption>

    @if (! $hasData)
        <p class="mt-6 rounded-xl bg-surface-muted px-4 py-8 text-center text-sm text-ink-muted">{{ $empty }}</p>
    @elseif ($orientation === 'column')
        <div class="mt-5 flex h-48 items-stretch gap-3" role="list" aria-label="{{ $title }}">
            @foreach ($data as $row)
                @php($pct = $row['value'] / $scale * 100)
                <div class="flex min-w-0 flex-1 flex-col" role="listitem">
                    <div class="relative flex flex-1 items-end justify-center border-b border-line">
                        <div tabindex="0" aria-label="{{ $row['label'] }}: {{ $fmt($row['value']) }}"
                             class="group/bar relative flex w-full max-w-6 flex-col items-center justify-end outline-none" style="height: {{ max($pct, 0) }}%">
                            <span class="absolute -top-6 text-xs font-semibold tabular-nums text-ink">{{ number_format($row['value']) }}</span>
                            <span class="h-full w-full rounded-t bg-chart transition-colors group-hover/bar:bg-chart-strong group-focus-visible/bar:bg-chart-strong {{ $row['value'] > 0 ? 'min-h-1' : '' }}"></span>
                            <span role="tooltip" class="pointer-events-none absolute bottom-full z-10 mb-7 hidden whitespace-nowrap rounded-lg bg-ink px-2.5 py-1.5 text-xs text-white shadow-lg group-hover/bar:block group-focus-visible/bar:block">
                                <strong class="font-semibold">{{ $fmt($row['value']) }}</strong> · {{ $row['label'] }}
                            </span>
                        </div>
                    </div>
                    <span class="mt-2 truncate text-center text-xs text-ink-muted">{{ $row['label'] }}</span>
                </div>
            @endforeach
        </div>
    @else
        <ul class="mt-5 space-y-2.5" aria-label="{{ $title }}">
            @foreach ($data as $row)
                @php($pct = $row['value'] / $scale * 100)
                <li class="grid grid-cols-[minmax(6rem,11rem)_1fr] items-center gap-3">
                    <span class="flex min-w-0 items-center gap-2 text-sm text-ink">
                        @isset($row['icon'])
                            <x-dynamic-component :component="$row['icon']" class="size-4 shrink-0 text-ink-muted" aria-hidden="true" />
                        @endisset
                        <span class="truncate">{{ $row['label'] }}</span>
                    </span>
                    <span class="flex items-center gap-2">
                        <span tabindex="0" aria-label="{{ $row['label'] }}: {{ $fmt($row['value']) }}"
                              class="group/bar relative block h-5 rounded-e bg-chart outline-none transition-colors hover:bg-chart-strong focus-visible:bg-chart-strong {{ $row['value'] > 0 ? 'min-w-1' : 'min-w-0' }}"
                              style="width: {{ $pct }}%">
                            <span role="tooltip" class="pointer-events-none absolute bottom-full left-0 z-10 mb-1 hidden whitespace-nowrap rounded-lg bg-ink px-2.5 py-1.5 text-xs text-white shadow-lg group-hover/bar:block group-focus-visible/bar:block">
                                <strong class="font-semibold">{{ $fmt($row['value']) }}</strong> · {{ $row['label'] }}
                            </span>
                        </span>
                        <span class="text-sm font-semibold tabular-nums text-ink">{{ number_format($row['value']) }}</span>
                    </span>
                </li>
            @endforeach
        </ul>
    @endif

    @if ($hasData)
        <details class="mt-4 text-sm">
            <summary class="cursor-pointer font-medium text-primary-700 hover:underline">Show as table</summary>
            <table class="mt-2 w-full text-start">
                <caption class="sr-only">{{ $title }}</caption>
                <thead><tr class="border-b border-line"><th scope="col" class="py-1.5 text-start font-semibold text-ink">Item</th><th scope="col" class="py-1.5 text-end font-semibold text-ink">Value</th></tr></thead>
                <tbody>
                    @foreach ($data as $row)
                        <tr class="border-b border-line last:border-0"><td class="py-1.5 text-ink">{{ $row['label'] }}</td><td class="py-1.5 text-end tabular-nums text-ink">{{ number_format($row['value']) }}</td></tr>
                    @endforeach
                </tbody>
            </table>
        </details>
    @endif
</figure>
