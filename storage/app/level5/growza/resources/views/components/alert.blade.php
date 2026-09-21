@props(['variant' => 'info', 'title' => null, 'dismissible' => false])

@php
    $variants = [
        'success' => ['bg' => 'bg-success-50 border-success-500 text-success-700'],
        'warning' => ['bg' => 'bg-warning-50 border-warning-500 text-warning-700'],
        'danger' => ['bg' => 'bg-danger-50 border-danger-500 text-danger-700'],
        'info' => ['bg' => 'bg-info-50 border-info-500 text-info-700'],
    ];
    $style = $variants[$variant] ?? $variants['info'];
@endphp

<div
    @if($dismissible) x-data="{ show: true }" x-show="show" @endif
    {{ $attributes->merge(['class' => 'border-l-4 rounded px-4 py-3 text-sm ' . $style['bg']]) }}
    role="alert"
>
    <div class="flex items-start justify-between gap-3">
        <div>
            @if ($title)
                <p class="font-medium mb-0.5">{{ $title }}</p>
            @endif
            <div>{{ $slot }}</div>
        </div>

        @if ($dismissible)
            <button type="button" @click="show = false" class="shrink-0 opacity-60 hover:opacity-100" aria-label="Dismiss">
                &times;
            </button>
        @endif
    </div>
</div>
