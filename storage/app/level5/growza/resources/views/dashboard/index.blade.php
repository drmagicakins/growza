@php use App\Support\Money; @endphp

<x-layouts.dashboard title="Dashboard" heading="Dashboard">

    <div class="mb-6">
        <h2 class="font-display text-xl text-ink-900">Welcome back, {{ auth()->user()->name }}</h2>
        <p class="mt-1 text-sm text-ink-500">Here's where things stand right now.</p>
    </div>

    {{-- The six metrics LEVEL 5 requires. Every figure is genuinely zero
         today — wallets (LEVEL 8), orders (LEVEL 7) and referral
         commissions (LEVEL 14) do not exist yet, so zero is the accurate
         value, not a placeholder standing in for something else. See
         CustomerDashboardMetricsService for exactly what replaces each
         one and when. --}}
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <x-card>
            <p class="text-sm text-ink-500">Wallet balance</p>
            <p class="mt-1.5 text-2xl font-display text-ink-900">{{ Money::format($metrics->walletBalanceMinor) }}</p>
        </x-card>
        <x-card>
            <p class="text-sm text-ink-500">Total orders</p>
            <p class="mt-1.5 text-2xl font-display text-ink-900">{{ $metrics->totalOrders }}</p>
        </x-card>
        <x-card>
            <p class="text-sm text-ink-500">Active orders</p>
            <p class="mt-1.5 text-2xl font-display text-ink-900">{{ $metrics->activeOrders }}</p>
        </x-card>
        <x-card>
            <p class="text-sm text-ink-500">Completed orders</p>
            <p class="mt-1.5 text-2xl font-display text-ink-900">{{ $metrics->completedOrders }}</p>
        </x-card>
        <x-card>
            <p class="text-sm text-ink-500">Total spent</p>
            <p class="mt-1.5 text-2xl font-display text-ink-900">{{ Money::format($metrics->totalSpentMinor) }}</p>
        </x-card>
        <x-card>
            <p class="text-sm text-ink-500">Referral earnings</p>
            <p class="mt-1.5 text-2xl font-display text-ink-900">{{ Money::format($metrics->referralEarningsMinor) }}</p>
        </x-card>
    </div>

    <div class="mt-8">
        <x-card :padded="false">
            <x-empty-state
                title="No campaigns yet"
                description="Create your first campaign and start growing your digital presence."
            >
                <x-slot:action>
                    <x-button as="a" href="{{ route('dashboard.services') }}" variant="primary" size="sm">
                        Browse services
                    </x-button>
                </x-slot:action>
            </x-empty-state>
        </x-card>
    </div>
</x-layouts.dashboard>
