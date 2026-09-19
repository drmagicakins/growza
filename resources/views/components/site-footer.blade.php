<footer class="border-t border-ink-200 bg-white mt-24">
    <div class="max-w-content mx-auto px-6 py-14">
        <div class="grid gap-10 md:grid-cols-4">
            <div class="md:col-span-1">
                <p class="font-display text-lg font-semibold text-ink-900">Growza</p>
                <p class="mt-2 text-sm text-ink-500 max-w-xs">Grow Smarter. Reach Further.</p>
                <p class="mt-4 text-sm text-ink-500">
                    Marketing, campaign management and audience growth for creators, artists, brands and agencies.
                </p>
            </div>

            <div>
                <h2 class="text-sm font-semibold text-ink-900">Platform</h2>
                <ul class="mt-3 space-y-2 text-sm">
                    <li><a href="{{ route('services') }}" class="text-ink-600 hover:text-ink-900">Services</a></li>
                    <li><a href="{{ route('pricing') }}" class="text-ink-600 hover:text-ink-900">Pricing</a></li>
                    <li><a href="{{ route('how-it-works') }}" class="text-ink-600 hover:text-ink-900">How it works</a></li>
                    <li><a href="{{ route('why-growza') }}" class="text-ink-600 hover:text-ink-900">Why Growza</a></li>
                </ul>
            </div>

            <div>
                <h2 class="text-sm font-semibold text-ink-900">Support</h2>
                <ul class="mt-3 space-y-2 text-sm">
                    <li><a href="{{ route('faq') }}" class="text-ink-600 hover:text-ink-900">FAQ</a></li>
                    <li><a href="{{ route('contact') }}" class="text-ink-600 hover:text-ink-900">Contact</a></li>
                    <li><a href="mailto:{{ config('marketing.contact.email') }}" class="text-ink-600 hover:text-ink-900">{{ config('marketing.contact.email') }}</a></li>
                </ul>
            </div>

            <div>
                <h2 class="text-sm font-semibold text-ink-900">Legal</h2>
                <ul class="mt-3 space-y-2 text-sm">
                    <li><a href="{{ route('legal.terms') }}" class="text-ink-600 hover:text-ink-900">Terms of Service</a></li>
                    <li><a href="{{ route('legal.privacy') }}" class="text-ink-600 hover:text-ink-900">Privacy Policy</a></li>
                    <li><a href="{{ route('legal.refund-policy') }}" class="text-ink-600 hover:text-ink-900">Refund Policy</a></li>
                    <li><a href="{{ route('legal.acceptable-use') }}" class="text-ink-600 hover:text-ink-900">Acceptable Use</a></li>
                </ul>
            </div>
        </div>

        <div class="mt-12 pt-6 border-t border-ink-100 flex flex-col sm:flex-row gap-3 sm:items-center sm:justify-between">
            <p class="text-sm text-ink-500">&copy; {{ date('Y') }} Growza. All rights reserved.</p>
            <p class="text-sm text-ink-400">
                Growza does not sell followers, likes, plays or views.
                <a href="{{ route('legal.acceptable-use') }}" class="underline hover:text-ink-700">Read our acceptable use policy</a>.
            </p>
        </div>
    </div>
</footer>
