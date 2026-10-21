{{--
    Label + <select> + hint + error.
    <x-select-field name="gender" label="Gender" :options="['male' => 'Boy', …]" placeholder="Choose…" required />
--}}
@props([
    'name',
    'label',
    'options' => [],
    'value' => null,
    'placeholder' => null,
    'hint' => null,
    'required' => false,
    'id' => null,
])

@php
    $id = $id ?? $name;
    $messages = $errors->get($name);
    $selected = (string) old($name, $value);
    $describedBy = collect([$hint ? "{$id}-hint" : null, $messages ? "{$id}-error" : null])->filter()->implode(' ');
@endphp

<div>
    <x-input-label :for="$id" :value="$label" :required="$required" />

    <select id="{{ $id }}" name="{{ $name }}" @required($required)
            @if ($messages) aria-invalid="true" @endif
            @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif
            {{ $attributes->merge(['class' => 'mt-1.5 block w-full rounded-lg border-line-input bg-surface text-ink shadow-xs focus:border-primary-600 focus:ring-primary-600 aria-invalid:border-danger-600']) }}>
        @if ($placeholder !== null)
            <option value="">{{ $placeholder }}</option>
        @endif
        @foreach ($options as $optionValue => $optionLabel)
            <option value="{{ $optionValue }}" @selected($selected === (string) $optionValue)>{{ $optionLabel }}</option>
        @endforeach
    </select>

    @if ($hint)
        <p id="{{ $id }}-hint" class="mt-1.5 text-sm text-ink-muted">{{ $hint }}</p>
    @endif

    <x-input-error :messages="$messages" id="{{ $id }}-error" />
</div>
