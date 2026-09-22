@props(['name', 'title' => null, 'maxWidth' => 'md'])

@php
    $maxWidths = ['sm' => 'max-w-sm', 'md' => 'max-w-md', 'lg' => 'max-w-lg', 'xl' => 'max-w-xl'];
@endphp

{{--
    Usage: dispatch a browser event to open/close, e.g.
    <x-button x-on:click="$dispatch('open-modal-{{ '{{' }} $name {{ '}}' }}')">Open</x-button>
    See DESIGN_SYSTEM.md "Modal" section for the full pattern and an
    accessibility note (focus trap is intentionally NOT implemented here —
    flagged as a LEVEL 29 follow-up, not silently assumed handled).
--}}
<div
    x-data="{ open: false }"
    x-on:open-modal-{{ $name }}.window="open = true"
    x-on:keydown.escape.window="open = false"
    x-show="open"
    x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center p-4"
>
    <div x-show="open" x-transition.opacity class="absolute inset-0 bg-ink-950/50" x-on:click="open = false"></div>

    <div
        x-show="open"
        x-transition
        class="relative bg-white rounded-lg shadow-floating w-full {{ $maxWidths[$maxWidth] ?? $maxWidths['md'] }} p-6"
        role="dialog"
        aria-modal="true"
    >
        @if ($title)
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-ink-900">{{ $title }}</h3>
                <button type="button" x-on:click="open = false" class="text-ink-400 hover:text-ink-700" aria-label="Close">&times;</button>
            </div>
        @endif

        {{ $slot }}
    </div>
</div>
