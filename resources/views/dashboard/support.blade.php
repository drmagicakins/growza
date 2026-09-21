<x-layouts.dashboard title="Support" heading="Support">
    <x-card :padded="false">
        <x-empty-state
            title="Support tickets aren't live yet"
            description="Until they are, reach us through the contact page and we'll reply directly to your email."
        >
            <x-slot:action>
                <x-button as="a" href="{{ route('contact') }}" variant="primary" size="sm">Contact us</x-button>
            </x-slot:action>
        </x-empty-state>
    </x-card>
</x-layouts.dashboard>
