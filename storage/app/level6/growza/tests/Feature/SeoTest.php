<?php

use function Pest\Laravel\get;

it('serves a sitemap listing the public marketing pages', function () {
    $response = get('/sitemap.xml');

    $response->assertOk()
        ->assertHeader('Content-Type', 'application/xml')
        ->assertSee(route('services'), escape: false)
        ->assertSee(route('pricing'), escape: false);
});

it('never lists private areas in the sitemap', function () {
    $response = get('/sitemap.xml');

    $response->assertDontSee('/dashboard', escape: false)
        ->assertDontSee('/admin', escape: false)
        ->assertDontSee('/dev/', escape: false);
});

it('blocks all crawling when indexing is disabled', function () {
    config()->set('app.allow_indexing', false);

    get('/robots.txt')->assertOk()->assertSee('Disallow: /', escape: false);
});

it('allows crawling but protects private areas when indexing is enabled', function () {
    config()->set('app.allow_indexing', true);

    get('/robots.txt')->assertOk()
        ->assertSee('Disallow: /dashboard', escape: false)
        ->assertSee('Disallow: /admin', escape: false)
        ->assertSee('Sitemap:', escape: false);
});

it('marks pages noindex when indexing is disabled', function () {
    config()->set('app.allow_indexing', false);

    get(route('home'))->assertSee('noindex', escape: false);
});

it('emits a canonical url on marketing pages', function () {
    get(route('pricing'))->assertSee('rel="canonical"', escape: false);
});

it('emits FAQ structured data on the faq page', function () {
    get(route('faq'))->assertSee('FAQPage', escape: false);
});
