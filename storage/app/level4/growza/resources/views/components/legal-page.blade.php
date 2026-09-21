@props(['title', 'updated' => null])

<x-layouts.marketing :seo-title="$title">
    <x-page-header eyebrow="Legal" :title="$title" />

    <div class="max-w-content mx-auto px-6 py-16">
        <div class="max-w-prose">
            <x-legal-review-notice />

            @if ($updated)
                <p class="text-sm text-ink-500 mb-8">Last updated: {{ $updated }}</p>
            @endif

            <div class="prose-growza space-y-6">
                {{ $slot }}
            </div>
        </div>
    </div>
</x-layouts.marketing>
