<x-layouts.auth title="Two-factor authentication" heading="Two-factor authentication">

    <x-auth-errors />

    {{-- Two panels toggled client-side. Both forms post to the same
         endpoint; Fortify decides which field it received. Alpine only
         controls which is visible — no logic depends on it. --}}
    <div x-data="{ recovery: false }">
        <div x-show="! recovery">
            <p class="text-sm text-ink-600 mb-5">
                Enter the 6-digit code from your authenticator app.
            </p>

            <form method="POST" action="{{ route('two-factor.login') }}" class="space-y-5">
                @csrf
                <x-input name="code" label="Authentication code" inputmode="numeric"
                         autocomplete="one-time-code" autofocus />
                <x-button type="submit" variant="primary" size="lg" class="w-full justify-center">Verify</x-button>
            </form>
        </div>

        <div x-show="recovery" x-cloak>
            <p class="text-sm text-ink-600 mb-5">
                Enter one of your emergency recovery codes. Each code can only be used once.
            </p>

            <form method="POST" action="{{ route('two-factor.login') }}" class="space-y-5">
                @csrf
                <x-input name="recovery_code" label="Recovery code" autocomplete="one-time-code" />
                <x-button type="submit" variant="primary" size="lg" class="w-full justify-center">Verify</x-button>
            </form>
        </div>

        <div class="mt-5 text-center">
            <button type="button" x-on:click="recovery = ! recovery"
                    class="text-sm text-ember-700 hover:text-ember-600">
                <span x-show="! recovery">Use a recovery code instead</span>
                <span x-show="recovery" x-cloak>Use an authentication code instead</span>
            </button>
        </div>
    </div>
</x-layouts.auth>
