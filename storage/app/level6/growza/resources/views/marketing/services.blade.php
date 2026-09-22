<x-layouts.marketing
    seo-title="Services"
    seo-description="Paid social campaigns, content strategy, music promotion, creator partnerships, search visibility and campaign analytics — delivered through legitimate channels.">

    <x-page-header
        eyebrow="Services"
        title="Campaign work, scoped and measured"
        subtitle="Every listed service links through to full pricing, delivery time and requirements."
    />

    <div class="max-w-content mx-auto px-6 py-20 space-y-16">
        @forelse ($categories as $category)
            <section class="border-b border-ink-200 pb-16 last:border-0">
                <h2 class="text-display-sm font-display">{{ $category->name }}</h2>
                @if ($category->description)
                    <p class="mt-2 text-ink-600 max-w-prose">{{ $category->description }}</p>
                @endif

                <div class="mt-8 grid gap-6 md:grid-cols-2">
                    @foreach ($category->services as $service)
                        <a href="{{ route('services.show', $service) }}" class="block">
                            <x-card class="h-full hover:border-ink-400 transition-colors">
                                <div class="flex items-start justify-between gap-3">
                                    <h3 class="font-display text-lg text-ink-900">{{ $service->name }}</h3>
                                    @if ($service->platform)
                                        <x-badge variant="neutral">{{ $service->platform->name }}</x-badge>
                                    @endif
                                </div>
                                <p class="mt-2 text-sm text-ink-600">{{ $service->summary }}</p>
                                <p class="mt-4 text-sm text-ink-500">Delivery: {{ $service->deliveryEstimate() }}</p>
                            </x-card>
                        </a>
                    @endforeach
                </div>
            </section>
        @empty
            <x-empty-state
                title="No services published yet"
                description="Check back shortly, or get in touch and we will scope something directly."
            >
                <x-slot:action>
                    <x-button as="a" href="{{ route('contact') }}" variant="primary" size="sm">Contact us</x-button>
                </x-slot:action>
            </x-empty-state>
        @endforelse
    </div>

    <x-cta-band
        title="Not sure which of these you need?"
        description="Describe the outcome you are chasing and we will tell you which service actually fits — including if the answer is none of them."
    />
</x-layouts.marketing>
