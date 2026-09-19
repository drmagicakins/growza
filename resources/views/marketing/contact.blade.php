<x-layouts.marketing
    seo-title="Contact"
    seo-description="Tell us what you are trying to achieve and get a direct answer about whether Growza is the right fit.">

    <x-page-header
        eyebrow="Contact"
        title="Tell us what you are trying to move"
        subtitle="The more specific you are about the outcome, the more useful our reply will be."
    />

    <div class="max-w-content mx-auto px-6 py-20">
        <div class="grid gap-12 lg:grid-cols-3">
            <div class="lg:col-span-2 max-w-2xl">
                @if (session('status'))
                    <x-alert variant="success" title="Message received" class="mb-8">
                        {{ session('status') }}
                    </x-alert>
                @endif

                @if ($errors->any())
                    <x-alert variant="danger" title="Please check the form" class="mb-8">
                        Some details need correcting before we can send this.
                    </x-alert>
                @endif

                <form method="POST" action="{{ route('contact.store') }}" class="space-y-6">
                    @csrf

                    {{-- Honeypot: a field real users never see or fill. Bots that
                         auto-complete every input get rejected server-side.
                         aria-hidden + tabindex keep it out of assistive tech. --}}
                    <div class="hidden" aria-hidden="true">
                        <label for="website">Website</label>
                        <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
                    </div>

                    <div class="grid gap-6 sm:grid-cols-2">
                        <x-input name="name" label="Your name" :value="old('name')"
                                 :error="$errors->first('name')" required autocomplete="name" />
                        <x-input name="email" type="email" label="Email address" :value="old('email')"
                                 :error="$errors->first('email')" required autocomplete="email" />
                    </div>

                    <div class="grid gap-6 sm:grid-cols-2">
                        <x-input name="phone" type="tel" label="Phone (optional)" :value="old('phone')"
                                 :error="$errors->first('phone')" autocomplete="tel" />
                        <x-select name="subject" label="What is this about?" :error="$errors->first('subject')">
                            <option value="">Select a topic</option>
                            @foreach (\App\Domain\Support\Models\ContactMessage::SUBJECTS as $value => $label)
                                <option value="{{ $value }}" @selected(old('subject') === $value)>{{ $label }}</option>
                            @endforeach
                        </x-select>
                    </div>

                    <x-textarea name="message" label="Your message" rows="6"
                                :error="$errors->first('message')"
                                help="Include the outcome you are chasing, your timeline, and roughly what budget you are working with."
                                required>{{ old('message') }}</x-textarea>

                    <div class="flex items-center gap-4">
                        <x-button type="submit" variant="primary" size="lg">Send message</x-button>
                        <p class="text-sm text-ink-500">We reply within one business day.</p>
                    </div>
                </form>
            </div>

            <aside class="space-y-6">
                <x-card>
                    <h2 class="font-medium text-ink-900">Direct contact</h2>
                    <dl class="mt-3 space-y-2 text-sm">
                        <div>
                            <dt class="text-ink-500">Email</dt>
                            <dd><a href="mailto:{{ config('growza-marketing.company.email') }}" class="text-ink-800 hover:text-ember-600">{{ config('growza-marketing.company.email') }}</a></dd>
                        </div>
                        @if (config('growza-marketing.company.phone'))
                            <div>
                                <dt class="text-ink-500">Phone</dt>
                                <dd class="text-ink-800">{{ config('growza-marketing.company.phone') }}</dd>
                            </div>
                        @endif
                        @if (config('growza-marketing.company.address'))
                            <div>
                                <dt class="text-ink-500">Address</dt>
                                <dd class="text-ink-800">{{ config('growza-marketing.company.address') }}</dd>
                            </div>
                        @endif
                    </dl>
                </x-card>

                <x-card>
                    <h2 class="font-medium text-ink-900">Already a client?</h2>
                    <p class="mt-1.5 text-sm text-ink-600">Support requests raised from your dashboard get tracked against your account and answered faster.</p>
                    <div class="mt-4"><x-button as="a" href="{{ url('/login') }}" variant="secondary" size="sm">Log in</x-button></div>
                </x-card>
            </aside>
        </div>
    </div>
</x-layouts.marketing>
