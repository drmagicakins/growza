{{--
    Public marketing nav. The dashboard/admin nav is a separate component
    built at LEVEL 5/16 once those areas exist — this one is scoped to
    the LEVEL 2 marketing site only, so it isn't carrying auth-state
    logic it doesn't need yet.
--}}
<header class="border-b border-ink-200 bg-white" x-data="{ mobileOpen: false }">
    <div class="max-w-content mx-auto px-6 h-16 flex items-center justify-between">
        <a href="{{ url('/') }}" class="font-display text-xl font-semibold text-ink-900">Growza</a>

        <nav class="hidden md:flex items-center gap-8 text-sm text-ink-600">
            <a href="{{ url('/services') }}" class="hover:text-ink-900">Services</a>
            <a href="{{ url('/pricing') }}" class="hover:text-ink-900">Pricing</a>
            <a href="{{ url('/how-it-works') }}" class="hover:text-ink-900">How it works</a>
            <a href="{{ url('/faq') }}" class="hover:text-ink-900">FAQ</a>
        </nav>

        <div class="hidden md:flex items-center gap-3">
            <x-button as="a" href="{{ url('/login') }}" variant="ghost" size="sm">Log in</x-button>
            <x-button as="a" href="{{ url('/register') }}" variant="primary" size="sm">Get started</x-button>
        </div>

        <button
            type="button"
            class="md:hidden text-ink-700"
            x-on:click="mobileOpen = !mobileOpen"
            :aria-expanded="mobileOpen"
            aria-label="Toggle navigation menu"
        >
            <svg x-show="!mobileOpen" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            <svg x-show="mobileOpen" x-cloak class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>

    <div x-show="mobileOpen" x-cloak class="md:hidden border-t border-ink-200 px-6 py-4 space-y-3">
        <a href="{{ url('/services') }}" class="block text-ink-700">Services</a>
        <a href="{{ url('/pricing') }}" class="block text-ink-700">Pricing</a>
        <a href="{{ url('/how-it-works') }}" class="block text-ink-700">How it works</a>
        <a href="{{ url('/faq') }}" class="block text-ink-700">FAQ</a>
        <div class="pt-3 flex gap-3">
            <x-button as="a" href="{{ url('/login') }}" variant="secondary" size="sm" class="flex-1 justify-center">Log in</x-button>
            <x-button as="a" href="{{ url('/register') }}" variant="primary" size="sm" class="flex-1 justify-center">Get started</x-button>
        </div>
    </div>
</header>
