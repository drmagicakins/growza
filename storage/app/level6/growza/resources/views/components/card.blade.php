@props(['padded' => true])

<div {{ $attributes->merge(['class' => 'bg-white border border-ink-200 rounded-md shadow-resting ' . ($padded ? 'p-6' : '')]) }}>
    {{ $slot }}
</div>
