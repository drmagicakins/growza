@props(['href' => '#', 'as' => 'a'])

@if ($as === 'button')
    <button type="button" {{ $attributes->merge(['class' => 'w-full text-left block px-4 py-2 text-sm text-ink-700 hover:bg-ink-50']) }}>
        {{ $slot }}
    </button>
@else
    <a href="{{ $href }}" {{ $attributes->merge(['class' => 'block px-4 py-2 text-sm text-ink-700 hover:bg-ink-50']) }}>
        {{ $slot }}
    </a>
@endif
