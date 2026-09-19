<x-layouts.auth title="Dashboard" heading="You're signed in">
    <p class="text-sm text-ink-600">
        Your account is active and verified. The full dashboard — wallet balance, campaign
        metrics and order history — is not built yet.
    </p>

    <div class="mt-6 space-y-3">
        <x-button as="a" href="{{ route('settings.security') }}" variant="primary" class="w-full justify-center">
            Security settings
        </x-button>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <x-button type="submit" variant="secondary" class="w-full justify-center">Log out</x-button>
        </form>
    </div>
</x-layouts.auth>
