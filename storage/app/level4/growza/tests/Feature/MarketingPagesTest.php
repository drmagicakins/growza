<?php

use function Pest\Laravel\get;

it('renders every public marketing page successfully', function (string $routeName) {
    get(route($routeName))->assertOk();
})->with([
    'home',
    'services',
    'pricing',
    'how-it-works',
    'why-growza',
    'faq',
    'contact',
    'legal.terms',
    'legal.privacy',
    'legal.refund-policy',
    'legal.acceptable-use',
    'legal.cookie-policy',
]);

it('shows the Growza tagline on the homepage', function () {
    get(route('home'))->assertSee('Reach further', escape: false);
});

it('states plainly that artificial engagement is not sold', function () {
    get(route('home'))->assertSee('No artificial engagement', escape: false);
});

it('renders legal pages with the pending-review banner while unreviewed', function () {
    config()->set('growza-marketing.legal.reviewed', false);

    get(route('legal.terms'))->assertSee('pending legal review', escape: false);
});

it('hides the pending-review banner once legal review is recorded', function () {
    config()->set('growza-marketing.legal.reviewed', true);

    get(route('legal.terms'))->assertDontSee('pending legal review', escape: false);
});

it('rejects an unknown legal page with a 404', function () {
    // Guards the allow-list in MarketingPageController::legal().
    get('/legal/not-a-real-policy')->assertNotFound();
});

it('does not render testimonials while none are configured', function () {
    // Guards against invented social proof appearing on a live site.
    expect(config('growza-marketing.testimonials'))->toBeEmpty();

    get(route('home'))->assertDontSee('What people say', escape: false);
});
