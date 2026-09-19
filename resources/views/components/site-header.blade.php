@php
    $links = [
        ['route' => 'services', 'label' => 'Services'],
        ['route' => 'pricing', 'label' => 'Pricing'],
        ['route' => 'how-it-works', 'label' => 'How it works'],
        ['route' => 'why-growza', 'label' => 'Why Growza'],
        ['route' => 'faq', 'label' => 'FAQ'],
    ];
@endphp

<header class="border-b border-ink-200 bg-white sticky top-0 z-30" x-data="{ mobileOpen: false }">
    <div class="max-w-content mx-auto px-6 h-16 flex items-center justify-between">
        <a href="{{ route('home') }}" class="font-display text-xl font-semibold text-ink-900 tracking-tight">
            Growza
        </a>

        <nav class="hidden lg:flex items-center gap-7 text-sm" aria-label="Primary">
            @foreach ($links as $link)
                <a href="{{ route($link['route']) }}"
                   @class([
                       'transition-colors',
                       'text-ink-900 font-medium' => request()->routeIs($link['route']),
                       'text-ink-600 hover:text-ink-900' => ! request()->routeIs($link['route']),
                   ])
                   @if (request()->routeIs($link['route'])) aria-current="page" @endif
                >{{ $link['label'] }}</a>
            @endforeach
        </nav>

        <div class="hidden lg:flex items-center gap-3">
            {{-- Auth routes land at LEVEL 3. Until then these point at
                 /contact so the header never contains a dead link. --}}
            <x-button as="a" href="{{ route('contact') }}" variant="ghost" size="sm">Talk to us</x-button>
            <x-button as="a" href="{{ route('contact') }}" variant="primary" size="sm">Start a campaign</x-button>
        </div>

        <button type="button" class="lg:hidden text-ink-700 -mr-1 p-1" x-on:click="mobileOpen = !mobileOpen"
                :aria-expanded="mobileOpen" aria-controls="mobile-nav" aria-label="Toggle navigation menu">
            <svg x-show="!mobileOpen" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            <svg x-show="mobileOpen" x-cloak class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>

    <div id="mobile-nav" x-show="mobileOpen" x-cloak class="lg:hidden border-t border-ink-200 bg-white px-6 py-5">
        <nav class="space-y-1" aria-label="Mobile">
            @foreach ($links as $link)
                <a href="{{ route($link['route']) }}"
                   @class([
                       'block py-2.5 text-base',
                       'text-ink-900 font-medium' => request()->routeIs($link['route']),
                       'text-ink-600' => ! request()->routeIs($link['route']),
                   ])
                >{{ $link['label'] }}</a>
            @endforeach
        </nav>
        <div class="pt-4 mt-3 border-t border-ink-100 flex flex-col gap-2.5">
            <x-button as="a" href="{{ route('contact') }}" variant="secondary" size="md" class="justify-center">Talk to us</x-button>
            <x-button as="a" href="{{ route('contact') }}" variant="primary" size="md" class="justify-center">Start a campaign</x-button>
        </div>
    </div>
</header>
