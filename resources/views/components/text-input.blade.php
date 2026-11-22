@props(['disabled' => false, 'invalid' => false])

<input
    @disabled($disabled)
    @if ($invalid) aria-invalid="true" @endif
    {{ $attributes->merge([
        'class' => 'block w-full rounded-lg border-line-input bg-surface text-ink shadow-xs placeholder:text-ink-subtle '
            .'focus:border-primary-600 focus:ring-primary-600 disabled:bg-surface-muted disabled:text-ink-muted '
            .'aria-invalid:border-danger-600 aria-invalid:focus:border-danger-600 aria-invalid:focus:ring-danger-600',
    ]) }}
>
