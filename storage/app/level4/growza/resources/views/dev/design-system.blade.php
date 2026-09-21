@extends('layouts.app')

@section('title', 'Growza Design System')

@section('content')
<div class="max-w-content mx-auto px-6 py-12 space-y-16">

    <div>
        <h1 class="text-display-lg font-display">Growza Design System</h1>
        <p class="mt-2 text-ink-500">Living reference for LEVEL 1 — every token and component below is real, not a mockup. See <code>DESIGN_SYSTEM.md</code> for the written spec this page implements.</p>
    </div>

    {{-- Colors --}}
    <section>
        <h2 class="text-display-sm font-display mb-4">Colors</h2>
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
            @foreach ([50,100,200,300,400,500,600,700,800,900,950] as $shade)
                <div>
                    <div class="h-14 rounded border border-ink-200 bg-ink-{{ $shade }}"></div>
                    <p class="text-xs text-ink-500 mt-1">ink-{{ $shade }}</p>
                </div>
            @endforeach
        </div>
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mt-4">
            @foreach ([50,100,200,300,400,500,600,700,800,900] as $shade)
                <div>
                    <div class="h-14 rounded border border-ink-200 bg-ember-{{ $shade }}"></div>
                    <p class="text-xs text-ink-500 mt-1">ember-{{ $shade }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- Typography --}}
    <section>
        <h2 class="text-display-sm font-display mb-4">Typography</h2>
        <div class="space-y-3">
            <p class="text-display-xl font-display">Display XL — Fraunces</p>
            <p class="text-display-lg font-display">Display LG — Fraunces</p>
            <p class="text-display-md font-display">Display MD — Fraunces</p>
            <p class="text-display-sm font-display">Display SM — Fraunces</p>
            <p class="text-base">Body text — Public Sans, the default UI/body font at 15px base.</p>
            <p class="text-sm text-ink-500">Small / muted text — Public Sans 14px, ink-500.</p>
        </div>
    </section>

    {{-- Buttons --}}
    <section>
        <h2 class="text-display-sm font-display mb-4">Buttons</h2>
        <div class="flex flex-wrap gap-3">
            <x-button variant="primary">Primary</x-button>
            <x-button variant="secondary">Secondary</x-button>
            <x-button variant="ghost">Ghost</x-button>
            <x-button variant="accent">Accent</x-button>
            <x-button variant="danger">Danger</x-button>
            <x-button variant="primary" disabled>Disabled</x-button>
        </div>
    </section>

    {{-- Form fields --}}
    <section>
        <h2 class="text-display-sm font-display mb-4">Form Fields</h2>
        <div class="grid md:grid-cols-2 gap-6 max-w-2xl">
            <x-input name="example_name" label="Full name" placeholder="Akinyemi Mathew" help="As it appears on your ID." />
            <x-input name="example_error" label="Email" type="email" value="not-an-email" error="Enter a valid email address." />
            <x-select name="example_select" label="Platform" :options="['instagram' => 'Instagram', 'tiktok' => 'TikTok']" />
            <x-textarea name="example_textarea" label="Message" placeholder="How can we help?" />
        </div>
    </section>

    {{-- Cards / Badges --}}
    <section>
        <h2 class="text-display-sm font-display mb-4">Cards &amp; Badges</h2>
        <div class="grid md:grid-cols-3 gap-4">
            <x-card>
                <p class="font-medium text-ink-900">Instagram Growth — Standard</p>
                <p class="text-sm text-ink-500 mt-1">1,000–10,000 followers · 24–48h delivery</p>
                <div class="mt-3 flex gap-2">
                    <x-badge variant="success">Active</x-badge>
                    <x-badge variant="accent">Popular</x-badge>
                </div>
            </x-card>
            <x-card>
                <p class="font-medium text-ink-900">TikTok Engagement</p>
                <p class="text-sm text-ink-500 mt-1">Views &amp; shares · Instant start</p>
                <div class="mt-3"><x-badge variant="warning">Limited availability</x-badge></div>
            </x-card>
            <x-card>
                <p class="font-medium text-ink-900">YouTube Watch Time</p>
                <p class="text-sm text-ink-500 mt-1">Monetization-track eligible</p>
                <div class="mt-3"><x-badge variant="neutral">Coming soon</x-badge></div>
            </x-card>
        </div>
    </section>

    {{-- Alerts --}}
    <section>
        <h2 class="text-display-sm font-display mb-4">Alerts</h2>
        <div class="space-y-3">
            <x-alert variant="success" title="Payment received">Your wallet has been credited with ₦25,000.</x-alert>
            <x-alert variant="warning" title="Verification pending">Please verify your email to place an order.</x-alert>
            <x-alert variant="danger" title="Something went wrong">Your wallet was not charged. Please try again.</x-alert>
            <x-alert variant="info" dismissible>New: Spotify and Audiomack services are now live.</x-alert>
        </div>
    </section>

    {{-- Table --}}
    <section>
        <h2 class="text-display-sm font-display mb-4">Table</h2>
        <x-table :headers="['Reference', 'Service', 'Status', 'Amount']">
            <tr>
                <td class="px-4 py-3 font-medium text-ink-800">GZ-2026-000042</td>
                <td class="px-4 py-3 text-ink-600">Instagram Growth</td>
                <td class="px-4 py-3"><x-badge variant="success">Completed</x-badge></td>
                <td class="px-4 py-3 text-ink-800">₦4,500</td>
            </tr>
            <tr>
                <td class="px-4 py-3 font-medium text-ink-800">GZ-2026-000043</td>
                <td class="px-4 py-3 text-ink-600">TikTok Engagement</td>
                <td class="px-4 py-3"><x-badge variant="warning">Processing</x-badge></td>
                <td class="px-4 py-3 text-ink-800">₦2,000</td>
            </tr>
        </x-table>
    </section>

    {{-- Empty / Loading states --}}
    <section>
        <h2 class="text-display-sm font-display mb-4">Empty &amp; Loading States</h2>
        <div class="grid md:grid-cols-2 gap-4">
            <x-card :padded="false">
                <x-empty-state title="No campaigns yet" description="Create your first campaign and start growing your digital presence.">
                    <x-slot:action><x-button variant="primary" size="sm">Create campaign</x-button></x-slot:action>
                </x-empty-state>
            </x-card>
            <x-card :padded="false">
                <x-loading-state label="Fetching your orders…" />
            </x-card>
        </div>
    </section>

    {{-- Breadcrumbs --}}
    <section>
        <h2 class="text-display-sm font-display mb-4">Breadcrumbs</h2>
        <x-breadcrumbs :items="[
            ['label' => 'Dashboard', 'href' => '#'],
            ['label' => 'Orders', 'href' => '#'],
            ['label' => 'GZ-2026-000042'],
        ]" />
    </section>

    {{-- Modal / Dropdown --}}
    <section>
        <h2 class="text-display-sm font-display mb-4">Modal &amp; Dropdown</h2>
        <div class="flex gap-4">
            <x-button x-on:click="$dispatch('open-modal-demo')">Open modal</x-button>

            <x-dropdown>
                <x-slot:trigger><x-button variant="secondary">Actions ▾</x-button></x-slot:trigger>
                <x-dropdown-item href="#">View order</x-dropdown-item>
                <x-dropdown-item href="#">Download receipt</x-dropdown-item>
                <x-dropdown-item as="button">Cancel order</x-dropdown-item>
            </x-dropdown>
        </div>

        <x-modal name="demo" title="Fund wallet">
            <p class="text-sm text-ink-600 mb-4">Enter an amount to add to your Growza wallet.</p>
            <x-input name="amount" label="Amount (₦)" type="number" placeholder="5000" />
            <div class="mt-5 flex justify-end gap-2">
                <x-button variant="secondary" x-on:click="open = false">Cancel</x-button>
                <x-button variant="primary">Continue to payment</x-button>
            </div>
        </x-modal>
    </section>

</div>
@endsection
