{{--
    Label + input + hint + validation error, wired together for screen readers.
    <x-field name="email" label="Email" type="email" required autocomplete="email" />
    Extra attributes go to the <input>. Pass :value to override old('name').
--}}
@props([
    'name',
    'label',
    'type' => 'text',
    'value' => null,
    'hint' => null,
    'required' => false,
    'id' => null,
    'errorBag' => 'default',
])

@php
    $id = $id ?? $name;
    $messages = $errors->getBag($errorBag)->get($name);
    $describedBy = collect([$hint ? "{$id}-hint" : null, $messages ? "{$id}-error" : null])->filter()->implode(' ');
@endphp

<div>
    <x-input-label :for="$id" :value="$label" :required="$required" />

    <x-text-input
        :id="$id"
        :name="$name"
        :type="$type"
        :value="$type === 'password' ? null : old($name, $value)"
        :required="$required"
        :invalid="(bool) $messages"
        :aria-describedby="$describedBy ?: null"
        {{ $attributes->merge(['class' => 'mt-1.5']) }}
    />

    @if ($hint)
        <p id="{{ $id }}-hint" class="mt-1.5 text-sm text-ink-muted">{{ $hint }}</p>
    @endif

    <x-input-error :messages="$messages" id="{{ $id }}-error" />
</div>
