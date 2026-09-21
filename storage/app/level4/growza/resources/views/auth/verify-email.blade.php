<x-layouts.auth title="Verify your email" heading="Verify your email"
                subheading="We sent a verification link to your email address.">

    @if (session('status') === 'verification-link-sent')
        <x-alert variant="success" class="mb-6">
            A new verification link has been sent to your email address.
        </x-alert>
    @endif

    <p class="text-sm text-ink-600">
        Click the link in that email to confirm your address. You will need a verified email before
        placing your first order.
    </p>

    <div class="mt-6 flex flex-col sm:flex-row gap-3">
        <form method="POST" action="{{ route('verification.send') }}" class="flex-1">
            @csrf
            <x-button type="submit" variant="primary" class="w-full justify-center">Resend the link</x-button>
        </form>

        <form method="POST" action="{{ route('logout') }}" class="flex-1">
            @csrf
            <x-button type="submit" variant="secondary" class="w-full justify-center">Log out</x-button>
        </form>
    </div>

    <p class="mt-6 text-sm text-ink-500">
        Wrong address? Log out and register again, or
        <a href="{{ route('contact') }}" class="text-ember-700 hover:text-ember-600">contact support</a>.
    </p>
</x-layouts.auth>
