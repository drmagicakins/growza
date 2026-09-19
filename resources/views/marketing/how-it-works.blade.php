<x-layouts.marketing
    seo-title="How it works"
    seo-description="From objective to scoped campaign to live tracking — how a Growza campaign runs, step by step.">

    <x-page-header
        eyebrow="How it works"
        title="From objective to reporting, in five steps"
        subtitle="Nothing happens to your money without a record you can inspect."
    />

    <div class="max-w-content mx-auto px-6 py-20">
        <ol class="space-y-12 max-w-3xl">
            @foreach (config('growza-marketing.process') as $item)
                <li class="flex gap-8">
                    <span class="font-display text-display-sm text-ink-200 shrink-0 w-16">{{ $item['step'] }}</span>
                    <div class="pt-1">
                        <h2 class="font-display text-xl text-ink-900">{{ $item['title'] }}</h2>
                        <p class="mt-2 text-ink-600 max-w-prose">{{ $item['description'] }}</p>
                    </div>
                </li>
            @endforeach
        </ol>

        <div class="mt-16 max-w-3xl">
            <h2 class="text-display-sm font-display">What you see in your dashboard</h2>
            <div class="mt-6 grid gap-6 sm:grid-cols-2">
                <div><h3 class="font-medium text-ink-900">Wallet balance and history</h3><p class="mt-1 text-sm text-ink-600">Every credit and debit, with a reference you can reconcile.</p></div>
                <div><h3 class="font-medium text-ink-900">Live campaign status</h3><p class="mt-1 text-sm text-ink-600">Queued, processing, partially completed or completed — never a silent gap.</p></div>
                <div><h3 class="font-medium text-ink-900">Order history</h3><p class="mt-1 text-sm text-ink-600">Each order keeps its own status timeline showing what changed and when.</p></div>
                <div><h3 class="font-medium text-ink-900">Reporting</h3><p class="mt-1 text-sm text-ink-600">Results measured against the objective you set at the start.</p></div>
            </div>
        </div>
    </div>

    <x-cta-band />
</x-layouts.marketing>
