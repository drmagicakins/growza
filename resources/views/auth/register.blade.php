<x-layouts.auth title="Create account" heading="Create your account" subheading="Set up a Growza account to fund a wallet and run campaigns.">

    <x-auth-errors />

    <form method="POST" action="{{ route('register') }}" class="space-y-5">
        @csrf

        <x-input name="name" label="Full name" :value="old('name')" required autofocus autocomplete="name" />

        <x-input name="email" type="email" label="Email address" :value="old('email')" required autocomplete="email" />

        <x-input name="phone" type="tel" label="Phone number" :value="old('phone')" required autocomplete="tel"
                 help="Used for account recovery and order updates." />

        <x-input name="password" type="password" label="Password" required autocomplete="new-password"
                 help="At least 10 characters, including letters and numbers." />

        <x-input name="password_confirmation" type="password" label="Confirm password" required autocomplete="new-password" />

        <x-input name="referral_code" label="Referral code (optional)" :value="old('referral_code', request('ref'))"
                 help="If someone referred you, enter their code here." />

        <label for="terms" class="flex items-start gap-2.5 text-sm text-ink-600">
            <input type="checkbox" id="terms" name="terms" value="1" required
                   class="mt-0.5 rounded border-ink-300 text-ink-900 focus:ring-ember-500">
            <span>
                I agree to the
                <a href="{{ route('legal.terms') }}" class="text-ember-700 hover:text-ember-600" target="_blank" rel="noopener">Terms of Service</a>,
                <a href="{{ route('legal.acceptable-use') }}" class="text-ember-700 hover:text-ember-600" target="_blank" rel="noopener">Acceptable Use Policy</a>
                and
                <a href="{{ route('legal.privacy') }}" class="text-ember-700 hover:text-ember-600" target="_blank" rel="noopener">Privacy Policy</a>.
            </span>
        </label>

        <x-button type="submit" variant="primary" size="lg" class="w-full justify-center">Create account</x-button>
    </form>

    <x-slot:footer>
        Already have an account?
        <a href="{{ route('login') }}" class="text-ember-700 hover:text-ember-600 font-medium">Log in</a>
    </x-slot:footer>
</x-layouts.auth>
