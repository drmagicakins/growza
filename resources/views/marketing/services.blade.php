<x-layouts.marketing
    seo-title="Services"
    seo-description="Paid social campaigns, content strategy, music promotion, creator partnerships, search visibility and campaign analytics — delivered through legitimate channels.">

    <x-page-header
        eyebrow="Services"
        title="Campaign work, scoped and measured"
        subtitle="Six service lines. Each one starts from a defined objective and reports against it."
    />

    <div class="max-w-content mx-auto px-6 py-20 space-y-16">
        @foreach (config('growza-marketing.services') as $service)
            <section class="grid gap-8 md:grid-cols-3 border-b border-ink-200 pb-16 last:border-0">
                <div class="md:col-span-1">
                    <h2 class="text-display-sm font-display">{{ $service['name'] }}</h2>
                    @if (! empty($service['platforms']))
                        <div class="mt-4 flex flex-wrap gap-1.5">
                            @foreach ($service['platforms'] as $platform)
                                <x-badge variant="neutral">{{ $platform }}</x-badge>
                            @endforeach
                        </div>
                    @endif
                </div>
                <div class="md:col-span-2 max-w-prose">
                    <p class="text-lg text-ink-700">{{ $service['summary'] }}</p>
                    <p class="mt-4 text-ink-600">{{ $service['detail'] }}</p>
                </div>
            </section>
        @endforeach
    </div>

    <x-cta-band
        title="Not sure which of these you need?"
        description="Describe the outcome you are chasing and we will tell you which service actually fits — including if the answer is none of them."
    />
</x-layouts.marketing>
