{{-- The vaccines being booked, with a link back to change them. Needs $child, $doses. --}}
<div class="mb-6 flex flex-wrap items-center gap-2 rounded-xl bg-surface p-4 ring-1 ring-line">
    <span class="text-sm font-medium text-ink-muted">Booking:</span>
    @foreach ($doses as $dose)
        <x-badge tone="primary">{{ $dose->label() }}</x-badge>
    @endforeach
    <a href="{{ route('parent.book.doses', $child) }}" class="ms-auto text-sm font-semibold text-primary-700 hover:underline">Change</a>
</div>
