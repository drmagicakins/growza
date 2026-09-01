@props(['label' => null, 'name', 'error' => null, 'help' => null, 'rows' => 4])

<div>
    @if ($label)
        <label for="{{ $name }}" class="field-label">{{ $label }}</label>
    @endif

    <textarea
        id="{{ $name }}"
        name="{{ $name }}"
        rows="{{ $rows }}"
        {{ $attributes->merge([
            'class' => 'block w-full rounded border px-3.5 py-2.5 text-sm text-ink-900 placeholder:text-ink-400 '
                . 'bg-white transition-colors '
                . ($error ? 'border-danger-500' : 'border-ink-200 focus:border-ink-400')
        ]) }}
    >{{ $slot }}</textarea>

    @if ($error)
        <p class="field-error">{{ $error }}</p>
    @elseif ($help)
        <p class="field-help">{{ $help }}</p>
    @endif
</div>
