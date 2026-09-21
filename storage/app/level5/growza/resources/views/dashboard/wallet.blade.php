<x-layouts.dashboard title="Wallet" heading="Wallet">
    <x-card>
        <p class="text-sm text-ink-500">Wallet balance</p>
        <p class="mt-1.5 text-3xl font-display text-ink-900">{{ \App\Support\Money::format(0) }}</p>
    </x-card>

    <div class="mt-6">
        <x-card :padded="false">
            <x-empty-state
                title="Wallet funding isn't live yet"
                description="Once payments are enabled, you'll be able to fund your wallet by card or bank transfer and see every transaction here."
            />
        </x-card>
    </div>
</x-layouts.dashboard>
