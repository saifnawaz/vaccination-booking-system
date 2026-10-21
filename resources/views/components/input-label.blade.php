@props(['value' => null, 'required' => false])

<label {{ $attributes->merge(['class' => 'block text-sm font-medium text-ink']) }}>
    {{ $value ?? $slot }}
    @if ($required)
        <span class="text-danger-700" aria-hidden="true">*</span>
        <span class="sr-only">(required)</span>
    @endif
</label>
