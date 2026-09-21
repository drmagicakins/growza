@php
    $seoDescription = 'Growza is a digital growth platform for creators, musicians, businesses and agencies. Managed campaigns, content strategy and creator partnerships — measured against real outcomes.';
@endphp

<x-layouts.marketing :seo-description="$seoDescription">

    {{-- Hero. Deliberately typographic rather than a gradient/illustration
         hero — see DESIGN_SYSTEM.md on avoiding the generic AI-SaaS look. --}}
    <section class="border-b border-ink-200">
        <div class="max-w-content mx-auto px-6 py-20 md:py-28">
            <div class="max-w-3xl">
                <p class="text-sm font-medium text-ember-600 mb-4">Digital growth, run properly</p>
                <h1 class="text-display-lg md:text-display-xl font-display leading-[1.05]">
                    Grow smarter.<br>Reach further.
                </h1>
                <p class="mt-6 text-lg text-ink-600 max-w-prose">
                    Growza runs campaigns for creators, musicians, businesses and agencies through the platforms'
                    own advertising and editorial channels — with a wallet, order history and reporting that
                    show exactly where every naira went.
                </p>
                <div class="mt-8 flex flex-wrap gap-3">
                    <x-button as="a" href="{{ route('register') }}" variant="primary" size="lg">Create an account</x-button>
                    <x-button as="a" href="{{ route('how-it-works') }}" variant="secondary" size="lg">See how it works</x-button>
                </div>
                <p class="mt-6 text-sm text-ink-500">
                    No artificial engagement. No bought followers. Nothing that risks your accounts.
                </p>
            </div>
        </div>
    </section>

    <section class="border-b border-ink-200 bg-ink-50">
        <div class="max-w-content mx-auto px-6 py-10">
            <p class="text-sm text-ink-500 mb-4">Campaigns delivered across</p>
            <ul class="flex flex-wrap gap-x-8 gap-y-3">
                @foreach (config('growza-marketing.platforms') as $platform)
                    <li class="text-ink-700 font-medium">{{ $platform }}</li>
                @endforeach
            </ul>
        </div>
    </section>

    <section class="max-w-content mx-auto px-6 py-20 md:py-24">
        <x-section-heading
            eyebrow="What we do"
            title="Services built around outcomes, not impressions"
            subtitle="Every engagement starts with the result you are trying to move, then works backwards to the channel."
        />

        <div class="mt-12 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
            @foreach (config('growza-marketing.services') as $service)
                <x-card class="flex flex-col">
                    <h3 class="font-display text-lg text-ink-900">{{ $service['name'] }}</h3>
                    <p class="mt-2 text-sm text-ink-600 flex-1">{{ $service['summary'] }}</p>
                    @if (! empty($service['platforms']))
                        <div class="mt-4 flex flex-wrap gap-1.5">
                            @foreach (array_slice($service['platforms'], 0, 4) as $platform)
                                <x-badge variant="neutral">{{ $platform }}</x-badge>
                            @endforeach
                        </div>
                    @endif
                </x-card>
            @endforeach
        </div>

        <div class="mt-8">
            <x-button as="a" href="{{ route('services') }}" variant="secondary">All services</x-button>
        </div>
    </section>

    <section class="bg-ink-50 border-y border-ink-200">
        <div class="max-w-content mx-auto px-6 py-20 md:py-24">
            <x-section-heading eyebrow="Who it's for" title="Built for people who need the numbers to mean something" />

            <dl class="mt-12 grid gap-x-10 gap-y-8 md:grid-cols-2 lg:grid-cols-3">
                @foreach (config('growza-marketing.audiences') as $audience)
                    <div>
                        <dt class="font-medium text-ink-900">{{ $audience['name'] }}</dt>
                        <dd class="mt-1.5 text-sm text-ink-600">{{ $audience['description'] }}</dd>
                    </div>
                @endforeach
            </dl>
        </div>
    </section>

    <section class="max-w-content mx-auto px-6 py-20 md:py-24">
        <x-section-heading
            eyebrow="How it works"
            title="Five steps, no black boxes"
            subtitle="You can see the status of everything you have paid for, at every stage."
        />

        <ol class="mt-12 space-y-8 max-w-3xl">
            @foreach (config('growza-marketing.process') as $item)
                <li class="flex gap-6">
                    <span class="font-display text-2xl text-ink-300 shrink-0 w-10">{{ $item['step'] }}</span>
                    <div>
                        <h3 class="font-medium text-ink-900">{{ $item['title'] }}</h3>
                        <p class="mt-1 text-ink-600">{{ $item['description'] }}</p>
                    </div>
                </li>
            @endforeach
        </ol>
    </section>

    <section class="bg-ink-50 border-y border-ink-200">
        <div class="max-w-content mx-auto px-6 py-20 md:py-24">
            <x-section-heading eyebrow="Why Growza" title="What makes this different" />

            <div class="mt-12 grid gap-8 md:grid-cols-3">
                <div>
                    <h3 class="font-medium text-ink-900">Legitimate by design</h3>
                    <p class="mt-2 text-sm text-ink-600">
                        We do not sell followers, likes, streams or bot traffic. Everything runs through
                        official advertising products and legitimate editorial channels, so your accounts
                        stay in good standing.
                    </p>
                </div>
                <div>
                    <h3 class="font-medium text-ink-900">Auditable spending</h3>
                    <p class="mt-2 text-sm text-ink-600">
                        Every wallet credit and debit carries a reference, a timestamp and a description.
                        You can reconcile your Growza statement against your bank statement line by line.
                    </p>
                </div>
                <div>
                    <h3 class="font-medium text-ink-900">You keep your accounts</h3>
                    <p class="mt-2 text-sm text-ink-600">
                        We request access to your ad accounts rather than asking for your passwords, and
                        that access can be revoked whenever you choose.
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- Testimonials render only when real, permissioned quotes exist.
         See the note in config/growza-marketing.php on why this is empty. --}}
    @if (! empty(config('growza-marketing.testimonials')))
        <section class="max-w-content mx-auto px-6 py-20 md:py-24">
            <x-section-heading eyebrow="Clients" title="What people say" />
            <div class="mt-12 grid gap-6 md:grid-cols-3">
                @foreach (config('growza-marketing.testimonials') as $testimonial)
                    <x-card>
                        <blockquote class="text-ink-700">{{ $testimonial['quote'] }}</blockquote>
                        <footer class="mt-4 text-sm text-ink-500">
                            <span class="font-medium text-ink-800">{{ $testimonial['name'] }}</span>
                        </footer>
                    </x-card>
                @endforeach
            </div>
        </section>
    @endif

    <section class="max-w-content mx-auto px-6 py-20 md:py-24">
        <x-section-heading eyebrow="Questions" title="Common questions" />
        <div class="mt-8 max-w-3xl">
            @foreach (array_slice(config('growza-marketing.faqs'), 0, 4) as $index => $faq)
                <x-faq-item :question="$faq['question']" :open="$index === 0">
                    {{ $faq['answer'] }}
                </x-faq-item>
            @endforeach
        </div>
        <div class="mt-8">
            <x-button as="a" href="{{ route('faq') }}" variant="secondary">All questions</x-button>
        </div>
    </section>

    <x-cta-band />

</x-layouts.marketing>
