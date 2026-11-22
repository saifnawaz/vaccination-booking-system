@props([
    'name',
    'label',
    'value' => null,
    'hint' => null,
    'required' => false,
    'rows' => 3,
    'id' => null,
])

@php
    $id = $id ?? $name;
    $messages = $errors->get($name);
    $describedBy = collect([$hint ? "{$id}-hint" : null, $messages ? "{$id}-error" : null])->filter()->implode(' ');
@endphp

<div>
    <x-input-label :for="$id" :value="$label" :required="$required" />

    <textarea id="{{ $id }}" name="{{ $name }}" rows="{{ $rows }}" @required($required)
              @if ($messages) aria-invalid="true" @endif
              @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif
              {{ $attributes->merge(['class' => 'mt-1.5 block w-full rounded-lg border-line-input bg-surface text-ink shadow-xs placeholder:text-ink-subtle focus:border-primary-600 focus:ring-primary-600 aria-invalid:border-danger-600']) }}>{{ old($name, $value) }}</textarea>

    @if ($hint)
        <p id="{{ $id }}-hint" class="mt-1.5 text-sm text-ink-muted">{{ $hint }}</p>
    @endif

    <x-input-error :messages="$messages" id="{{ $id }}-error" />
</div>
