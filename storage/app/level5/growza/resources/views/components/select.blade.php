@props(['label' => null, 'name', 'error' => null, 'help' => null, 'options' => []])

<div>
    @if ($label)
        <label for="{{ $name }}" class="field-label">{{ $label }}</label>
    @endif

    <select
        id="{{ $name }}"
        name="{{ $name }}"
        {{ $attributes->merge([
            'class' => 'block w-full rounded border px-3.5 py-2.5 text-sm text-ink-900 bg-white transition-colors '
                . ($error ? 'border-danger-500' : 'border-ink-200 focus:border-ink-400')
        ]) }}
    >
        @foreach ($options as $value => $label)
            <option value="{{ $value }}">{{ $label }}</option>
        @endforeach
        {{ $slot }}
    </select>

    @if ($error)
        <p class="field-error">{{ $error }}</p>
    @elseif ($help)
        <p class="field-help">{{ $help }}</p>
    @endif
</div>
