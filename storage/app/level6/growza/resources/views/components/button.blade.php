@props([
    'variant' => 'primary', // primary | secondary | ghost | danger
    'size' => 'md',         // sm | md | lg
    'as' => 'button',        // button | a
])

@php
    $variants = [
        'primary' => 'bg-ink-900 text-white hover:bg-ink-800 active:bg-ink-950 disabled:bg-ink-300',
        'secondary' => 'bg-white text-ink-800 border border-ink-200 hover:bg-ink-50 active:bg-ink-100 disabled:text-ink-300',
        'ghost' => 'bg-transparent text-ink-700 hover:bg-ink-100 active:bg-ink-200 disabled:text-ink-300',
        'danger' => 'bg-danger-500 text-white hover:bg-danger-700 active:bg-danger-700 disabled:bg-danger-50 disabled:text-danger-500',
        'accent' => 'bg-ember-500 text-ink-950 hover:bg-ember-400 active:bg-ember-600 disabled:bg-ember-100',
    ];

    $sizes = [
        'sm' => 'text-sm px-3 py-1.5 gap-1.5',
        'md' => 'text-sm px-4 py-2.5 gap-2',
        'lg' => 'text-base px-5 py-3 gap-2',
    ];

    $classes = 'inline-flex items-center justify-center rounded font-medium transition-colors duration-150 '
        . 'disabled:cursor-not-allowed '
        . ($variants[$variant] ?? $variants['primary']) . ' '
        . ($sizes[$size] ?? $sizes['md']);
@endphp

@if ($as === 'a')
    <a {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</a>
@else
    <button {{ $attributes->merge(['type' => 'button', 'class' => $classes]) }}>{{ $slot }}</button>
@endif
