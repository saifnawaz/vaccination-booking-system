{{-- A labelled checkbox. Pass name/value/checked; extra attributes go to the <input>. --}}
@props(['label', 'hint' => null, 'checked' => false])

<label class="flex items-start gap-3">
    <input type="checkbox" @checked($checked)
           {{ $attributes->merge(['class' => 'mt-0.5 size-4 shrink-0 rounded border-line-input text-primary-700 focus:ring-primary-600']) }}>
    <span>
        <span class="block text-sm font-medium text-ink">{{ $label }}</span>
        @if ($hint)
            <span class="block text-sm text-ink-muted">{{ $hint }}</span>
        @endif
    </span>
</label>
