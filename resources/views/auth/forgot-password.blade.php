<x-layouts.auth title="Forgot password" heading="Reset your password"
                subheading="Enter your email and we will send you a reset link.">

    <x-auth-errors />

    <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
        @csrf
        <x-input name="email" type="email" label="Email address" :value="old('email')" required autofocus autocomplete="email" />
        <x-button type="submit" variant="primary" size="lg" class="w-full justify-center">Send reset link</x-button>
    </form>

    <x-slot:footer>
        <a href="{{ route('login') }}" class="text-ember-700 hover:text-ember-600 font-medium">Back to log in</a>
    </x-slot:footer>
</x-layouts.auth>
