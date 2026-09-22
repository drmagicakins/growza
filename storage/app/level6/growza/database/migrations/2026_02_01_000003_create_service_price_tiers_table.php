<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Per-tier pricing overrides (retail / reseller / agency / enterprise —
 * ARCHITECTURE.md §20/§22). Created now, populated later: LEVEL 6 seeds
 * only 'retail' rows (equal to services.customer_price_minor) so the
 * table exists and is queryable; LEVEL 20 is what actually onboards
 * reseller/agency accounts and gives those tiers real, different prices.
 *
 * A null price_minor means "no override for this tier yet — fall back to
 * services.customer_price_minor", not "free".
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_price_tiers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->string('tier'); // retail | reseller | agency | enterprise
            $table->unsignedBigInteger('price_minor')->nullable();
            $table->timestamps();

            $table->unique(['service_id', 'tier']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_price_tiers');
    }
};
