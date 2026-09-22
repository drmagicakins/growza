<?php

namespace App\Domain\Catalogue\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Per-tier price override for a service (retail/reseller/agency/enterprise
 * — see the migration docblock). Read-facing only at LEVEL 6; LEVEL 20 is
 * what actually assigns accounts to a non-retail tier.
 */
class ServicePriceTier extends Model
{
    use HasFactory;

    protected $fillable = ['service_id', 'tier', 'price_minor'];

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}
