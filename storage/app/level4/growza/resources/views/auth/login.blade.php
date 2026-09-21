<x-layouts.auth title="Log in" heading="Welcome back" subheading="Log in to manage your campaigns and wallet.">

    <x-auth-errors />

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <x-input name="email" type="email" label="Email address"
                 :value="old('email')" required autofocus autocomplete="email" />

        <x-input name="password" type="password" label="Password"
                 required autocomplete="current-password" />

        <div class="flex items-center justify-between">
            <label for="remember" class="flex items-center gap-2 text-sm text-ink-600">
                <input type="checkbox" id="remember" name="remember"
                       class="rounded border-ink-300 text-ink-900 focus:ring-ember-500">
                <span>Remember me</span>
            </label>

            <a href="{{ route('password.request') }}" class="text-sm text-ember-700 hover:text-ember-600">
                Forgot password?
            </a>
        </div>

        <x-button type="submit" variant="primary" size="lg" class="w-full justify-center">Log in</x-button>
    </form>

    <x-slot:footer>
        Don't have an account?
        <a href="{{ route('register') }}" class="text-ember-700 hover:text-ember-600 font-medium">Create one</a>
    </x-slot:footer>
</x-layouts.auth>
