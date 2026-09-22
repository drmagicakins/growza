@props(['title', 'description' => null, 'icon' => null])

{{--
    LEVEL 1 requirement: empty states must explain what's missing AND
    what to do next — never a bare "No data." (see master prompt LEVEL 41).
    The action slot is where a caller drops a real <x-button>.
--}}
<div {{ $attributes->merge(['class' => 'text-center py-16 px-6']) }}>
    @if ($icon)
        <div class="mx-auto mb-4 w-12 h-12 flex items-center justify-center rounded-full bg-ink-100 text-ink-400">
            {{ $icon }}
        </div>
    @endif

    <h3 class="text-base font-semibold text-ink-900">{{ $title }}</h3>

    @if ($description)
        <p class="mt-1.5 text-sm text-ink-500 max-w-sm mx-auto">{{ $description }}</p>
    @endif

    @isset($action)
        <div class="mt-5">{{ $action }}</div>
    @endisset
</div>
