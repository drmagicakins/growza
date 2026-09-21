@props(['variant' => 'neutral']) {{-- neutral | success | warning | danger | info --}}

@php
    $variants = [
        'neutral' => 'bg-ink-100 text-ink-700',
        'success' => 'bg-success-50 text-success-700',
        'warning' => 'bg-warning-50 text-warning-700',
        'danger' => 'bg-danger-50 text-danger-700',
        'info' => 'bg-info-50 text-info-700',
        'accent' => 'bg-ember-50 text-ember-700',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center rounded-pill px-2.5 py-1 text-xs font-medium ' . ($variants[$variant] ?? $variants['neutral'])]) }}>
    {{ $slot }}
</span>
