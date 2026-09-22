@php use App\Support\Money; @endphp

<x-layouts.marketing
    :seo-title="$service->name"
    :seo-description="$service->summary">

    <div class="border-b border-ink-200 bg-ink-50">
        <div class="max-w-content mx-auto px-6 py-16">
            <x-breadcrumbs :items="[
                ['label' => 'Services', 'href' => route('services')],
                ['label' => $service->category->name, 'href' => route('services').'#'.$service->category->slug],
                ['label' => $service->name],
            ]" />

            <div class="mt-6 flex flex-wrap items-center gap-2">
                @if ($service->platform)
                    <x-badge variant="neutral">{{ $service->platform->name }}</x-badge>
                @endif
                <x-badge variant="neutral">{{ $service->category->name }}</x-badge>
            </div>

            <h1 class="mt-4 text-display-md md:text-display-lg font-display max-w-3xl">{{ $service->name }}</h1>
            <p class="mt-4 text-lg text-ink-600 max-w-prose">{{ $service->summary }}</p>
        </div>
    </div>

    <div class="max-w-content mx-auto px-6 py-16">
        <div class="grid gap-12 lg:grid-cols-3">
            <div class="lg:col-span-2 max-w-prose">
                @if ($service->description)
                    <h2 class="font-display text-xl text-ink-900 mb-3">What's included</h2>
                    <p class="text-ink-600">{{ $service->description }}</p>
                @endif

                @if ($service->requirements)
                    <h2 class="font-display text-xl text-ink-900 mt-8 mb-3">What we need from you</h2>
                    <p class="text-ink-600">{{ $service->requirements }}</p>
                @endif

                @if ($service->refund_policy_note)
                    <x-alert variant="info" class="mt-8">{{ $service->refund_policy_note }}</x-alert>
                @endif
            </div>

            <aside>
                <x-card>
                    <p class="text-sm text-ink-500">Pricing</p>

                    @if ($service->isFixedPrice())
                        <p class="mt-1.5 text-2xl font-display text-ink-900">
                            {{ Money::format($service->customer_price_minor ?? 0) }}
                        </p>
                        <p class="text-sm text-ink-500">one-time fee</p>
                    @else
                        <p class="mt-1.5 text-lg font-display text-ink-900">
                            {{ Money::format($service->min_budget_minor ?? 0) }} – {{ Money::format($service->max_budget_minor ?? 0) }}
                        </p>
                        <p class="text-sm text-ink-500">campaign budget, you choose within this range</p>
                        @if ($service->management_fee_minor)
                            <p class="mt-2 text-sm text-ink-600">
                                Plus a {{ Money::format($service->management_fee_minor) }} management fee
                            </p>
                        @endif
                    @endif

                    <hr class="my-4 border-ink-200">

                    <p class="text-sm text-ink-500">Estimated delivery</p>
                    <p class="mt-1 text-ink-800">{{ $service->deliveryEstimate() }}</p>

                    <div class="mt-6">
                        <x-button variant="primary" class="w-full justify-center" disabled>
                            Ordering opens soon
                        </x-button>
                        <p class="mt-2 text-xs text-ink-500 text-center">
                            <a href="{{ route('register') }}" class="text-ember-700 hover:text-ember-600">Create an account</a>
                            to be notified the moment checkout is live.
                        </p>
                    </div>
                </x-card>
            </aside>
        </div>
    </div>

    <x-cta-band
        title="Want this scoped for your specific situation?"
        description="Every campaign starts with a conversation about what you're actually trying to move."
    />
</x-layouts.marketing>
