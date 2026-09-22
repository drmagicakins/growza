<?php

namespace App\Domain\Catalogue\Models;

use App\Domain\Catalogue\Enums\PricingModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * An orderable catalogue entry (LEVEL 6). Ordering itself — turning a
 * Service into an Order — is LEVEL 7; this model is read-only from every
 * controller that exists today.
 */
class Service extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'platform_id',
        'service_category_id',
        'name',
        'slug',
        'summary',
        'description',
        'pricing_model',
        'min_budget_minor',
        'max_budget_minor',
        'management_fee_minor',
        'base_price_minor',
        'customer_price_minor',
        'estimated_delivery_min_days',
        'estimated_delivery_max_days',
        'requirements',
        'terms',
        'refund_policy_note',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'pricing_model' => PricingModel::class,
            'is_active' => 'boolean',
        ];
    }

    public function platform(): BelongsTo
    {
        return $this->belongsTo(Platform::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ServiceCategory::class, 'service_category_id');
    }

    public function priceTiers(): HasMany
    {
        return $this->hasMany(ServicePriceTier::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    public function isFixedPrice(): bool
    {
        return $this->pricing_model === PricingModel::Fixed;
    }

    /**
     * A human-readable delivery estimate, e.g. "1-2 days" or "3 days" when
     * min and max are equal. Presentation-only — no rounding or business
     * logic beyond formatting two integers.
     */
    public function deliveryEstimate(): string
    {
        if ($this->estimated_delivery_min_days === $this->estimated_delivery_max_days) {
            return $this->estimated_delivery_min_days.' day'.($this->estimated_delivery_min_days === 1 ? '' : 's');
        }

        return "{$this->estimated_delivery_min_days}-{$this->estimated_delivery_max_days} days";
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
