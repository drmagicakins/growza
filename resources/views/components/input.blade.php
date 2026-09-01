@props([
    'label' => null,
    'name',
    'type' => 'text',
    'error' => null,
    'help' => null,
])

<div>
    @if ($label)
        <label for="{{ $name }}" class="field-label">{{ $label }}</label>
    @endif

    <input
        id="{{ $name }}"
        name="{{ $name }}"
        type="{{ $type }}"
        {{ $attributes->merge([
            'class' => 'block w-full rounded border px-3.5 py-2.5 text-sm text-ink-900 placeholder:text-ink-400 '
                . 'bg-white transition-colors '
                . ($error ? 'border-danger-500' : 'border-ink-200 focus:border-ink-400')
        ]) }}
    />

    @if ($error)
        <p class="field-error">{{ $error }}</p>
    @elseif ($help)
        <p class="field-help">{{ $help }}</p>
    @endif
</div>
