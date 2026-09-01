@props(['align' => 'right'])

<div
    x-data="{ open: false }"
    class="relative inline-block"
>
    <div
        x-on:click.stop="open = !open"
        :aria-expanded="open"
    >
        {{ $trigger }}
    </div>

    <div
        x-show="open"
        x-cloak
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        x-on:click.outside="open = false"
        x-on:keydown.escape.window="open = false"
        class="absolute {{ $align === 'right' ? 'right-0' : 'left-0' }} mt-2 w-56 rounded-md bg-white border border-ink-200 shadow-raised py-1 z-40"
    >
        {{ $slot }}
    </div>
</div>