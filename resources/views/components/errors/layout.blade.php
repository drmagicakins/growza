{{-- Shared shell for branded error pages. Full error-handling work
     (422/419/429 handling, safe API envelopes, production detail
     suppression) is LEVEL 27 — this covers the two a public marketing
     visitor can realistically hit, so LEVEL 2 doesn't ship Laravel's
     unstyled default. --}}
@props(['code', 'title', 'message'])

<x-layouts.marketing :seo-title="$code.' — '.$title.' | Growza'" :seo-description="$message">
    <section class="max-w-content mx-auto px-6 py-24 md:py-32">
        <div class="max-w-xl">
            <p class="text-sm font-semibold text-ember-600 tracking-widest">{{ $code }}</p>
            <h1 class="mt-3 text-display-lg font-display">{{ $title }}</h1>
            <p class="mt-5 text-lg text-ink-600">{{ $message }}</p>
            <div class="mt-9 flex flex-col sm:flex-row gap-3">
                <x-button as="a" href="{{ route('home') }}" variant="primary">Back to homepage</x-button>
                <x-button as="a" href="{{ route('contact') }}" variant="secondary">Contact support</x-button>
            </div>
        </div>
    </section>
</x-layouts.marketing>
