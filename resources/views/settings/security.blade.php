<x-layouts.auth title="Security" heading="Security settings">

    <x-auth-errors bag="updatePassword" />

    @if (session('status') === 'password-updated')
        <x-alert variant="success" class="mb-6">Your password has been updated.</x-alert>
    @endif

    {{-- Password change --}}
    <section>
        <h2 class="font-medium text-ink-900">Change password</h2>
        <p class="mt-1 text-sm text-ink-500">You will be emailed whenever your password changes.</p>

        <form method="POST" action="{{ route('user-password.update') }}" class="mt-4 space-y-4">
            @csrf
            @method('PUT')

            <x-input name="current_password" type="password" label="Current password"
                     required autocomplete="current-password" />
            <x-input name="password" type="password" label="New password" required autocomplete="new-password"
                     help="At least 10 characters, including letters and numbers." />
            <x-input name="password_confirmation" type="password" label="Confirm new password"
                     required autocomplete="new-password" />

            <x-button type="submit" variant="primary" class="w-full justify-center">Update password</x-button>
        </form>
    </section>

    <hr class="my-8 border-ink-200">

    {{-- Two-factor authentication --}}
    <section>
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="font-medium text-ink-900">Two-factor authentication</h2>
                <p class="mt-1 text-sm text-ink-500">Require a code from your authenticator app when logging in.</p>
            </div>
            @if ($user->hasTwoFactorEnabled())
                <x-badge variant="success">Enabled</x-badge>
            @else
                <x-badge variant="neutral">Off</x-badge>
            @endif
        </div>

        @if (! $user->hasTwoFactorEnabled())
            <form method="POST" action="{{ route('two-factor.enable') }}" class="mt-4">
                @csrf
                <x-button type="submit" variant="primary" class="w-full justify-center">
                    Enable two-factor authentication
                </x-button>
            </form>

            @if ($user->two_factor_secret)
                {{-- Secret issued but not yet confirmed: show the QR code
                     and require a code before marking 2FA active, so a
                     user cannot lock themselves out with a misconfigured
                     authenticator app. --}}
                <div class="mt-6">
                    <p class="text-sm text-ink-600">Scan this with your authenticator app, then enter the code it shows.</p>
                    <div class="mt-4 inline-block bg-white p-3 border border-ink-200 rounded">
                        {!! $user->twoFactorQrCodeSvg() !!}
                    </div>

                    <form method="POST" action="{{ route('two-factor.confirm') }}" class="mt-4 space-y-4">
                        @csrf
                        <x-input name="code" label="Authentication code" inputmode="numeric" autocomplete="one-time-code" />
                        <x-button type="submit" variant="primary" class="w-full justify-center">Confirm and activate</x-button>
                    </form>
                </div>
            @endif
        @else
            <div class="mt-4 space-y-3">
                <details class="border border-ink-200 rounded p-4">
                    <summary class="cursor-pointer text-sm font-medium text-ink-800">Show recovery codes</summary>
                    <p class="mt-2 text-sm text-ink-500">
                        Store these somewhere safe. Each one can be used once if you lose access to your authenticator app.
                    </p>
                    <ul class="mt-3 space-y-1 font-mono text-sm text-ink-700">
                        @foreach (json_decode(decrypt($user->two_factor_recovery_codes), true) ?? [] as $code)
                            <li>{{ $code }}</li>
                        @endforeach
                    </ul>
                </details>

                <form method="POST" action="{{ route('two-factor.disable') }}">
                    @csrf
                    @method('DELETE')
                    <x-button type="submit" variant="danger" class="w-full justify-center">
                        Disable two-factor authentication
                    </x-button>
                </form>
            </div>
        @endif
    </section>

    <hr class="my-8 border-ink-200">

    <a href="{{ route('dashboard') }}" class="text-sm text-ember-700 hover:text-ember-600">Back to dashboard</a>
</x-layouts.auth>
