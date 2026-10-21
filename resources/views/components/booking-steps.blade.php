{{-- Progress through the booking flow. $current: 1 = vaccines, 2 = hospital, 3 = time, 4 = confirm. --}}
@props(['current'])

@php($steps = ['Vaccines', 'Hospital', 'Time', 'Confirm'])

<nav aria-label="Booking progress" class="mb-6">
    <ol class="flex items-center gap-2 text-sm">
        @foreach ($steps as $i => $label)
            @php($n = $i + 1)
            <li class="flex items-center gap-2 {{ $loop->last ? '' : 'flex-1' }}" @if ($n === $current) aria-current="step" @endif>
                <span @class([
                    'flex size-7 shrink-0 items-center justify-center rounded-full text-xs font-bold',
                    'bg-primary-700 text-white' => $n === $current,
                    'bg-success-50 text-success-700 ring-1 ring-success-100' => $n < $current,
                    'bg-surface-muted text-ink-subtle' => $n > $current,
                ])>
                    @if ($n < $current)
                        <x-heroicon-o-check-circle class="size-4" aria-hidden="true" />
                        <span class="sr-only">Done:</span>
                    @else
                        {{ $n }}
                    @endif
                </span>
                <span @class(['hidden sm:inline', 'font-semibold text-ink' => $n === $current, 'text-ink-muted' => $n !== $current])>{{ $label }}</span>
                @unless ($loop->last)
                    <span class="h-px flex-1 bg-line" aria-hidden="true"></span>
                @endunless
            </li>
        @endforeach
    </ol>
    <p class="mt-2 text-sm text-ink-muted sm:hidden">Step {{ $current }} of 4: {{ $steps[$current - 1] }}</p>
</nav>
