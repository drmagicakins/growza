<x-layouts.auth title="Confirm password" heading="Confirm your password"
                subheading="This is a sensitive area. Please confirm your password to continue.">

    <x-auth-errors />

    <form method="POST" action="{{ route('password.confirm.store') }}" class="space-y-5">
        @csrf
        <x-input name="password" type="password" label="Password" required autofocus autocomplete="current-password" />
        <x-button type="submit" variant="primary" size="lg" class="w-full justify-center">Confirm</x-button>
    </form>
</x-layouts.auth>
