<?php

use App\Domain\Catalogue\Enums\PricingModel;
use App\Domain\Catalogue\Models\Service;
use App\Domain\Catalogue\Models\ServiceCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function makeService(array $overrides = []): Service
{
    $category = ServiceCategory::create(['name' => 'Test Category', 'slug' => 'test-category-'.uniqid()]);

    return Service::create(array_merge([
        'service_category_id' => $category->id,
        'name' => 'Test Service',
        'slug' => 'test-service-'.uniqid(),
        'summary' => 'A test service.',
        'pricing_model' => PricingModel::Fixed->value,
        'customer_price_minor' => 10000,
        'estimated_delivery_min_days' => 3,
        'estimated_delivery_max_days' => 3,
        'is_active' => true,
    ], $overrides));
}

it('reports isFixedPrice correctly for each pricing model', function () {
    expect(makeService()->isFixedPrice())->toBeTrue()
        ->and(makeService(['pricing_model' => PricingModel::BudgetRange->value])->isFixedPrice())->toBeFalse();
});

it('formats a single-day delivery estimate in the singular', function () {
    expect(makeService(['estimated_delivery_min_days' => 1, 'estimated_delivery_max_days' => 1])->deliveryEstimate())
        ->toBe('1 day');
});

it('formats an equal multi-day delivery estimate in the plural', function () {
    expect(makeService(['estimated_delivery_min_days' => 3, 'estimated_delivery_max_days' => 3])->deliveryEstimate())
        ->toBe('3 days');
});

it('formats a differing delivery estimate as a range', function () {
    expect(makeService(['estimated_delivery_min_days' => 2, 'estimated_delivery_max_days' => 5])->deliveryEstimate())
        ->toBe('2-5 days');
});
