<x-layouts.dashboard title="Services" heading="Services">
    <x-alert variant="info" class="mb-6">
        Ordering isn't live yet — you can browse what's coming, and we'll email you the moment checkout opens.
    </x-alert>

    <div class="grid gap-6 md:grid-cols-2">
        @forelse ($services as $service)
            <x-card class="flex flex-col">
                <h3 class="font-display text-lg text-ink-900">{{ $service->name }}</h3>
                <p class="mt-2 text-sm text-ink-600 flex-1">{{ $service->summary }}</p>
                <div class="mt-4 flex flex-wrap gap-1.5">
                    @if ($service->platform)
                        <x-badge variant="neutral">{{ $service->platform->name }}</x-badge>
                    @endif
                    <x-badge variant="neutral">{{ $service->category->name }}</x-badge>
                </div>
                <div class="mt-4">
                    <x-badge variant="warning">Ordering opens soon</x-badge>
                </div>
            </x-card>
        @empty
            <p class="text-sm text-ink-500">No services published yet.</p>
        @endforelse
    </div>
</x-layouts.dashboard>
