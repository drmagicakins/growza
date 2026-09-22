@push('head')
    {{-- FAQPage structured data, generated from the same config the page
         renders — so the markup and the visible content can never drift. --}}
    <script type="application/ld+json">
        {!! json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => collect(config('growza-marketing.faqs'))->map(fn ($faq) => [
                '@type' => 'Question',
                'name' => $faq['question'],
                'acceptedAnswer' => ['@type' => 'Answer', 'text' => $faq['answer']],
            ])->all(),
        ], JSON_UNESCAPED_SLASHES) !!}
    </script>
@endpush

<x-layouts.marketing
    seo-title="FAQ"
    seo-description="Answers on wallets, refunds, account ownership, campaign timelines and why Growza does not sell artificial engagement.">

    <x-page-header eyebrow="FAQ" title="Questions people actually ask" />

    <div class="max-w-content mx-auto px-6 py-20">
        <div class="max-w-3xl">
            @foreach (config('growza-marketing.faqs') as $index => $faq)
                <x-faq-item :question="$faq['question']" :open="$index === 0">
                    {{ $faq['answer'] }}
                </x-faq-item>
            @endforeach
        </div>

        <div class="mt-12 max-w-3xl">
            <x-card>
                <h2 class="font-medium text-ink-900">Still not answered?</h2>
                <p class="mt-1.5 text-sm text-ink-600">Send us the specifics and you will get a direct answer, not a brochure.</p>
                <div class="mt-4"><x-button as="a" href="{{ route('contact') }}" variant="primary" size="sm">Contact us</x-button></div>
            </x-card>
        </div>
    </div>
</x-layouts.marketing>
