<x-layouts.auth title="Set a new password" heading="Set a new password">

    <x-auth-errors />

    <form method="POST" action="{{ route('password.update') }}" class="space-y-5">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <x-input name="email" type="email" label="Email address"
                 :value="old('email', $request->email)" required autocomplete="email" />

        <x-input name="password" type="password" label="New password" required autocomplete="new-password"
                 help="At least 10 characters, including letters and numbers." />

        <x-input name="password_confirmation" type="password" label="Confirm new password" required autocomplete="new-password" />

        <x-button type="submit" variant="primary" size="lg" class="w-full justify-center">Update password</x-button>
    </form>
</x-layouts.auth>
