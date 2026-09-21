<x-layouts.dashboard title="Settings" heading="Settings">
    <div class="grid gap-4 sm:grid-cols-2">
        <a href="{{ route('dashboard.profile') }}" class="block">
            <x-card class="hover:border-ink-400 transition-colors">
                <h2 class="font-medium text-ink-900">Profile</h2>
                <p class="mt-1 text-sm text-ink-500">Name, email address and phone number.</p>
            </x-card>
        </a>

        <a href="{{ route('settings.security') }}" class="block">
            <x-card class="hover:border-ink-400 transition-colors">
                <h2 class="font-medium text-ink-900">Security</h2>
                <p class="mt-1 text-sm text-ink-500">Password and two-factor authentication.</p>
            </x-card>
        </a>
    </div>
</x-layouts.dashboard>
