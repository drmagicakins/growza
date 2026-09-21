<x-layouts.dashboard title="Referrals" heading="Referrals">
    @if ($referredByCode)
        <x-alert variant="info" class="mb-6">
            Your account was registered with the referral code <strong>{{ $referredByCode }}</strong>.
        </x-alert>
    @endif

    <x-card :padded="false">
        <x-empty-state
            title="The referral program isn't live yet"
            description="Once it launches, you'll get your own code to share, and every qualifying signup will show up here with its commission status."
        />
    </x-card>
</x-layouts.dashboard>
