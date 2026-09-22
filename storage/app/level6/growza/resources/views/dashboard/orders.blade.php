<x-layouts.dashboard title="Orders" heading="Orders">
    <x-card :padded="false">
        <x-empty-state
            title="No orders yet"
            description="Once ordering opens, every campaign you place will show up here with live status tracking."
        >
            <x-slot:action>
                <x-button as="a" href="{{ route('dashboard.services') }}" variant="primary" size="sm">
                    Browse services
                </x-button>
            </x-slot:action>
        </x-empty-state>
    </x-card>
</x-layouts.dashboard>
