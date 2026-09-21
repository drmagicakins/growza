@props(['align' => 'right'])

<div x-data="{ open: false }" class="relative inline-block" x-on:keydown.escape.window="open = false">
    <div x-on:click="open = !open">
        {{ $trigger }}
    </div>

    <div
        x-show="open"
        x-cloak
        x-on:click.outside="open = false"
        x-transition
        class="absolute {{ $align === 'right' ? 'right-0' : 'left-0' }} mt-2 w-56 rounded-md bg-white border border-ink-200 shadow-raised py-1 z-40"
    >
        {{ $slot }}
    </div>
</div>
