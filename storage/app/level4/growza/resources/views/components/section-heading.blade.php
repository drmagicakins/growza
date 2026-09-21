@props(['title', 'subtitle' => null, 'eyebrow' => null, 'align' => 'left'])

<div class="{{ $align === 'center' ? 'text-center mx-auto max-w-2xl' : 'max-w-2xl' }}">
    @if ($eyebrow)
        <p class="text-sm font-medium text-ember-600 mb-2">{{ $eyebrow }}</p>
    @endif
    <h2 class="text-display-sm md:text-display-md font-display">{{ $title }}</h2>
    @if ($subtitle)
        <p class="mt-3 text-ink-600">{{ $subtitle }}</p>
    @endif
</div>
