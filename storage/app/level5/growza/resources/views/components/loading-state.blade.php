@props(['label' => 'Loading…'])

<div {{ $attributes->merge(['class' => 'flex items-center justify-center gap-2.5 py-10 text-ink-500 text-sm']) }} role="status">
    <svg class="animate-spin h-4 w-4 text-ink-400" viewBox="0 0 24 24" fill="none" aria-hidden="true">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
    </svg>
    <span>{{ $label }}</span>
</div>
