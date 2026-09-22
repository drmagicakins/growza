<x-layouts.marketing
    seo-title="Pricing"
    seo-description="Transparent campaign and management pricing. Fund your wallet, see exactly what goes to ad spend versus management.">

    <x-page-header
        eyebrow="Pricing"
        title="You should be able to tell what you are paying for"
        :subtitle="config('growza-marketing.pricing.note')"
    />

    <div class="max-w-content mx-auto px-6 py-20">
        <div class="grid gap-6 md:grid-cols-3 items-start">
            @foreach (config('growza-marketing.pricing.tiers') as $tier)
                <div class="rounded-md border p-6 {{ $tier['featured'] ? 'border-ink-900 shadow-raised' : 'border-ink-200 bg-white' }}">
                    @if ($tier['featured'])
                        <x-badge variant="accent" class="mb-3">Most chosen</x-badge>
                    @endif
                    <h2 class="font-display text-xl text-ink-900">{{ $tier['name'] }}</h2>
                    <p class="mt-3">
                        <span class="text-display-sm font-display text-ink-900">{{ $tier['price'] }}</span>
                        <span class="text-sm text-ink-500 block mt-1">{{ $tier['period'] }}</span>
                    </p>
                    <p class="mt-4 text-sm text-ink-600">{{ $tier['summary'] }}</p>

                    <ul class="mt-6 space-y-2.5">
                        @foreach ($tier['features'] as $feature)
                            <li class="flex gap-2.5 text-sm text-ink-700">
                                <svg class="w-4 h-4 mt-0.5 shrink-0 text-ember-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                </svg>
                                <span>{{ $feature }}</span>
                            </li>
                        @endforeach
                    </ul>

                    <div class="mt-8">
                        <x-button as="a" href="{{ route('register') }}"
                                  :variant="$tier['featured'] ? 'primary' : 'secondary'"
                                  class="w-full justify-center">
                            Get started
                        </x-button>
                    </div>
                </div>
            @endforeach
        </div>

        <x-alert variant="info" class="mt-12 max-w-3xl">
            Campaign budget is held in your Growza wallet and debited per order. Management fees and ad spend
            appear as separate transactions, so you can always see the split.
        </x-alert>
    </div>

    <x-cta-band />
</x-layouts.marketing>
