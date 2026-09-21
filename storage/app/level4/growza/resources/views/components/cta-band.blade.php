@props([
    'title' => 'Ready to grow with intent?',
    'description' => 'Create an account, tell us what you are trying to move, and get a scoped campaign plan before you spend anything.',
])

<section class="bg-ink-900">
    <div class="max-w-content mx-auto px-6 py-16 md:py-20">
        <div class="md:flex md:items-center md:justify-between md:gap-10">
            <div class="max-w-xl">
                <h2 class="text-display-sm md:text-display-md font-display text-white">{{ $title }}</h2>
                <p class="mt-3 text-ink-300">{{ $description }}</p>
            </div>
            <div class="mt-6 md:mt-0 flex flex-wrap gap-3 shrink-0">
                <x-button as="a" href="{{ route('register') }}" variant="accent" size="lg">Create an account</x-button>
                <x-button as="a" href="{{ route('contact') }}" size="lg"
                          class="bg-transparent text-white border border-ink-600 hover:bg-ink-800">Talk to us</x-button>
            </div>
        </div>
    </div>
</section>
