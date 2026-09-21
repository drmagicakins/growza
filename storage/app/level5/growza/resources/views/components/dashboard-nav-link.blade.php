@props(['href', 'active' => false, 'mobile' => false])

@php
    // Mobile touch targets are deliberately larger (py-3.5 vs py-2.5) —
    // a real adjustment for the input method, not the same markup shrunk
    // to fit. See DESIGN_SYSTEM.md / LEVEL 30.
    $padding = $mobile ? 'px-4 py-3.5' : 'px-3 py-2.5';
@endphp

<a
    href="{{ $href }}"
    {{ $attributes->merge([
        'class' => "flex items-center gap-3 {$padding} rounded text-sm font-medium transition-colors "
            . ($active ? 'bg-ink-900 text-white' : 'text-ink-600 hover:bg-ink-100 hover:text-ink-900')
    ]) }}
>
    {{ $slot }}
</a>
