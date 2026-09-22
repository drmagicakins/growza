<?php

use App\Domain\Catalogue\Models\Platform;
use App\Domain\Catalogue\Models\Service;
use App\Domain\Catalogue\Models\ServiceCategory;
use App\Domain\Catalogue\Models\ServicePriceTier;
use Database\Seeders\CatalogueSeeder;

use function Pest\Laravel\get;

beforeEach(fn () => (new CatalogueSeeder)->run());

it('seeds the nine platforms named in the master prompt', function () {
    expect(Platform::count())->toBe(9)
        ->and(Platform::where('name', 'Instagram')->exists())->toBeTrue()
        ->and(Platform::where('name', 'SoundCloud')->exists())->toBeTrue();
});

it('seeds six service categories', function () {
    expect(ServiceCategory::count())->toBe(6);
});

it('seeds at least one service per category', function () {
    foreach (ServiceCategory::all() as $category) {
        expect($category->services()->count())->toBeGreaterThan(0);
    }
});

it('gives every fixed-price service a retail price tier matching its customer price', function () {
    Service::where('pricing_model', 'fixed')->get()->each(function (Service $service) {
        $tier = ServicePriceTier::where('service_id', $service->id)->where('tier', 'retail')->first();

        expect($tier)->not->toBeNull()
            ->and($tier->price_minor)->toBe($service->customer_price_minor);
    });
});

it('is idempotent when run twice', function () {
    $before = Service::count();

    (new CatalogueSeeder)->run();

    expect(Service::count())->toBe($before);
});

it('renders the public services page grouped by real category', function () {
    get(route('services'))
        ->assertOk()
        ->assertSee('Paid Social', escape: false)
        ->assertSee('Instagram & Facebook Ad Campaign', escape: false);
});

it('renders a fixed-price service detail page with its price', function () {
    $service = Service::where('pricing_model', 'fixed')->firstOrFail();

    get(route('services.show', $service))
        ->assertOk()
        ->assertSee($service->name, escape: false)
        ->assertSee('one-time fee', escape: false);
});

it('renders a budget-range service detail page with a range, not a single price', function () {
    $service = Service::where('pricing_model', 'budget_range')->firstOrFail();

    get(route('services.show', $service))
        ->assertOk()
        ->assertSee('campaign budget, you choose within this range', escape: false);
});

it('404s for an inactive service', function () {
    $service = Service::first();
    $service->update(['is_active' => false]);

    get(route('services.show', $service))->assertNotFound();
});

it('404s for an unknown service slug', function () {
    get('/services/not-a-real-service')->assertNotFound();
});

it('shows real platform names on the homepage', function () {
    get(route('home'))->assertSee('SoundCloud', escape: false);
});

it('excludes an inactive service from the sitemap', function () {
    $service = Service::first();
    $service->update(['is_active' => false]);

    get('/sitemap.xml')->assertDontSee(route('services.show', $service), escape: false);
});

it('includes an active service in the sitemap', function () {
    $service = Service::active()->firstOrFail();

    get('/sitemap.xml')->assertSee(route('services.show', $service), escape: false);
});
