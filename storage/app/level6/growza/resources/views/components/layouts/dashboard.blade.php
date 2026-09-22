@props(['title' => null])

@php
    // Single source of truth for dashboard navigation — shared between the
    // desktop sidebar and the mobile drawer so the two can never drift.
    // 'route' names are checked defensively: LEVEL 5 links every one of
    // these, but a later level renaming a route here is a one-line fix
    // rather than a hunt through two separate nav markups.
    $navItems = [
        ['label' => 'Dashboard', 'route' => 'dashboard'],
        ['label' => 'Services', 'route' => 'dashboard.services'],
        ['label' => 'Orders', 'route' => 'dashboard.orders'],
        ['label' => 'Wallet', 'route' => 'dashboard.wallet'],
        ['label' => 'Transactions', 'route' => 'dashboard.transactions'],
        ['label' => 'Referrals', 'route' => 'dashboard.referrals'],
        ['label' => 'Support', 'route' => 'dashboard.support'],
        ['label' => 'Notifications', 'route' => 'dashboard.notifications'],
        ['label' => 'Settings', 'route' => 'dashboard.settings'],
    ];
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- Every dashboard page is private — never indexable, in any environment. --}}
    <x-seo :title="$title" :noindex="true" />

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,500;9..144,600&family=Public+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-ink-50 text-ink-800 font-sans" x-data="{ drawerOpen: false }">

    {{-- Desktop sidebar. Hidden entirely below md — this is not resized
         for mobile, it simply does not render there (see the drawer below
         for the mobile-native equivalent). --}}
    <aside class="hidden md:flex md:fixed md:inset-y-0 md:left-0 md:w-60 md:flex-col border-r border-ink-200 bg-white">
        <div class="h-16 flex items-center px-6 border-b border-ink-200">
            <a href="{{ route('home') }}" class="font-display text-lg font-semibold text-ink-900">Growza</a>
        </div>
        <nav class="flex-1 overflow-y-auto p-4 space-y-1">
            @foreach ($navItems as $item)
                <x-dashboard-nav-link :href="route($item['route'])" :active="request()->routeIs($item['route'])">
                    {{ $item['label'] }}
                </x-dashboard-nav-link>
            @endforeach
        </nav>
        <div class="p-4 border-t border-ink-200">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="text-sm text-ink-500 hover:text-ink-800">Log out</button>
            </form>
        </div>
    </aside>

    {{-- Mobile off-canvas drawer — a distinct interaction (full-height
         overlay, larger touch targets), not the sidebar above squeezed
         into a smaller viewport. --}}
    <div
        x-show="drawerOpen"
        x-cloak
        x-transition:enter="transition-opacity ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-40 bg-ink-950/50 md:hidden"
        x-on:click="drawerOpen = false"
    ></div>

    <div
        x-show="drawerOpen"
        x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="-translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="-translate-x-full"
        class="fixed inset-y-0 left-0 z-50 w-72 max-w-[85vw] bg-white flex flex-col md:hidden"
        role="dialog"
        aria-modal="true"
    >
        <div class="h-16 flex items-center justify-between px-5 border-b border-ink-200">
            <span class="font-display text-lg font-semibold text-ink-900">Growza</span>
            <button type="button" x-on:click="drawerOpen = false" class="text-ink-500" aria-label="Close menu">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <nav class="flex-1 overflow-y-auto p-4 space-y-1">
            @foreach ($navItems as $item)
                <x-dashboard-nav-link :href="route($item['route'])" :active="request()->routeIs($item['route'])" :mobile="true">
                    {{ $item['label'] }}
                </x-dashboard-nav-link>
            @endforeach
        </nav>
        <div class="p-4 border-t border-ink-200">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="text-sm text-ink-500 hover:text-ink-800 px-4 py-2">Log out</button>
            </form>
        </div>
    </div>

    {{-- Main column --}}
    <div class="md:pl-60">
        <header class="h-16 border-b border-ink-200 bg-white flex items-center justify-between px-4 md:px-8 sticky top-0 z-30">
            <div class="flex items-center gap-3">
                <button type="button" x-on:click="drawerOpen = true" class="md:hidden text-ink-700" aria-label="Open menu">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                @isset($heading)
                    <h1 class="font-display text-lg text-ink-900">{{ $heading }}</h1>
                @endisset
            </div>

            <x-dropdown align="right">
                <x-slot:trigger>
                    <button type="button" class="flex items-center gap-2 text-sm text-ink-700">
                        <span class="w-8 h-8 rounded-full bg-ink-900 text-white flex items-center justify-center text-xs font-medium">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </span>
                        <span class="hidden sm:inline">{{ auth()->user()->name }}</span>
                    </button>
                </x-slot:trigger>

                <x-dropdown-item :href="route('dashboard.settings')">Settings</x-dropdown-item>
                <x-dropdown-item :href="route('settings.security')">Security</x-dropdown-item>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-dropdown-item as="button" type="submit">Log out</x-dropdown-item>
                </form>
            </x-dropdown>
        </header>

        <main class="p-4 md:p-8 max-w-5xl">
            @if (session('status'))
                <x-alert variant="success" class="mb-6">{{ session('status') }}</x-alert>
            @endif

            {{ $slot }}
        </main>
    </div>
</body>
</html>
