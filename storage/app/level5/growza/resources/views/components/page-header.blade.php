@props(['title', 'subtitle' => null, 'eyebrow' => null])

<section class="border-b border-ink-200 bg-ink-50">
    <div class="max-w-content mx-auto px-6 py-16 md:py-20">
        @if ($eyebrow)
            <p class="text-sm font-medium text-ember-600 mb-3">{{ $eyebrow }}</p>
        @endif
        <h1 class="text-display-md md:text-display-lg font-display max-w-3xl">{{ $title }}</h1>
        @if ($subtitle)
            <p class="mt-4 text-lg text-ink-600 max-w-prose">{{ $subtitle }}</p>
        @endif
    </div>
</section>
