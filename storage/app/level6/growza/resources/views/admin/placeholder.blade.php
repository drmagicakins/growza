<x-layouts.auth title="Admin" heading="Admin access confirmed">
    <p class="text-sm text-ink-600">
        You reached this page because your account holds a role granting the
        <code class="text-xs bg-ink-100 px-1.5 py-0.5 rounded">view_users</code> permission. The real
        admin panel — Users, Orders, Payments, Providers, Reports and Settings modules — is built at LEVEL 16.
    </p>

    <div class="mt-6">
        <x-button as="a" href="{{ route('dashboard') }}" variant="secondary" class="w-full justify-center">
            Back to dashboard
        </x-button>
    </div>
</x-layouts.auth>
