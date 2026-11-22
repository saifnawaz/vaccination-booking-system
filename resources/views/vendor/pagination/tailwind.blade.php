{{-- Overrides Laravel's default pagination view with the app's design tokens. --}}
@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination" class="flex flex-col items-center justify-between gap-3 sm:flex-row">
        <p class="text-sm text-ink-muted">
            Showing <span class="font-semibold text-ink">{{ $paginator->firstItem() }}</span>
            to <span class="font-semibold text-ink">{{ $paginator->lastItem() }}</span>
            of <span class="font-semibold text-ink">{{ $paginator->total() }}</span>
        </p>

        @php
            $link = 'inline-flex min-w-10 items-center justify-center rounded-lg px-3 py-2 text-sm font-medium';
            $idle = $link.' text-ink ring-1 ring-inset ring-line-input bg-surface hover:bg-surface-muted';
            $off = $link.' text-ink-subtle ring-1 ring-inset ring-line bg-surface-muted cursor-not-allowed';
        @endphp

        <ul class="flex flex-wrap items-center gap-1.5">
            <li>
                @if ($paginator->onFirstPage())
                    <span class="{{ $off }}" aria-disabled="true">
                        <x-heroicon-o-chevron-left class="size-4" aria-hidden="true" /><span class="sr-only">Previous page</span>
                    </span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="{{ $idle }}">
                        <x-heroicon-o-chevron-left class="size-4" aria-hidden="true" /><span class="sr-only">Previous page</span>
                    </a>
                @endif
            </li>

            @foreach ($elements as $element)
                @if (is_string($element))
                    <li class="hidden sm:block"><span class="{{ $off }}" aria-hidden="true">{{ $element }}</span></li>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        <li class="hidden sm:block">
                            @if ($page == $paginator->currentPage())
                                <span aria-current="page" class="{{ $link }} bg-primary-700 text-white">{{ $page }}</span>
                            @else
                                <a href="{{ $url }}" class="{{ $idle }}" aria-label="Page {{ $page }}">{{ $page }}</a>
                            @endif
                        </li>
                    @endforeach
                @endif
            @endforeach

            <li>
                @if ($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="{{ $idle }}">
                        <x-heroicon-o-chevron-right class="size-4" aria-hidden="true" /><span class="sr-only">Next page</span>
                    </a>
                @else
                    <span class="{{ $off }}" aria-disabled="true">
                        <x-heroicon-o-chevron-right class="size-4" aria-hidden="true" /><span class="sr-only">Next page</span>
                    </span>
                @endif
            </li>
        </ul>
    </nav>
@endif
