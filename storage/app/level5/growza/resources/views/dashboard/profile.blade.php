<x-layouts.dashboard title="Profile" heading="Profile">

    <x-auth-errors bag="updateProfileInformation" />

    @if (session('status') === 'profile-information-updated')
        <x-alert variant="success" class="mb-6">Your profile has been updated.</x-alert>
    @endif

    <x-card class="max-w-xl">
        <form method="POST" action="{{ route('user-profile-information.update') }}" class="space-y-5">
            @csrf
            @method('PUT')

            <x-input name="name" label="Full name" :value="old('name', auth()->user()->name)" required />
            <x-input name="email" type="email" label="Email address" :value="old('email', auth()->user()->email)" required
                      help="Changing this will require you to verify the new address." />
            <x-input name="phone" type="tel" label="Phone number" :value="old('phone', auth()->user()->phone)" required />

            <x-button type="submit" variant="primary">Save changes</x-button>
        </form>
    </x-card>

    <div class="mt-6">
        <a href="{{ route('dashboard.settings') }}" class="text-sm text-ember-700 hover:text-ember-600">Back to settings</a>
    </div>
</x-layouts.dashboard>
