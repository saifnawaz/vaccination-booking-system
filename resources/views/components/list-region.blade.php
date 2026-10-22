{{--
    Wraps a list so a skeleton can stand in while it reloads. When a filter form
    (data-filter) on the page is submitted or a pagination link is followed, the list
    is swapped for placeholder rows until the next page arrives (resources/js/app.js).
    <x-list-region :rows="6"> …list… </x-list-region>
    layout: "rows" (tables and stacked lists) or "cards" (grids of cards).
--}}
@props(['rows' => 6, 'layout' => 'rows'])

<div data-list-region {{ $attributes->merge(['class' => 'relative']) }}>
    <div data-list-content>{{ $slot }}</div>

    <div data-list-skeleton hidden aria-hidden="true">
        @if ($layout === 'cards')
            <div class="grid gap-4 sm:grid-cols-2">
                @for ($i = 0; $i < min($rows, 4); $i++)
                    <div class="rounded-2xl bg-card p-5 ring-1 ring-line">
                        <div class="flex items-center gap-3">
                            <div class="skeleton size-11 rounded-full"></div>
                            <div class="flex-1 space-y-2"><div class="skeleton h-4 w-1/2"></div><div class="skeleton h-3 w-1/3"></div></div>
                        </div>
                        <div class="skeleton mt-5 h-2 w-full"></div>
                        <div class="skeleton mt-4 h-3 w-2/3"></div>
                    </div>
                @endfor
            </div>
        @else
            <div class="rounded-2xl bg-card p-5 ring-1 ring-line">
                <div class="skeleton h-4 w-1/4"></div>
                @for ($i = 0; $i < $rows; $i++)
                    <div class="mt-4 flex items-center gap-4">
                        <div class="skeleton h-4 w-1/3"></div>
                        <div class="skeleton h-4 flex-1"></div>
                        <div class="skeleton h-4 w-16"></div>
                    </div>
                @endfor
            </div>
        @endif
    </div>
</div>
