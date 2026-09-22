<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * LEVEL 6 orderable catalogue. Deliberately has NO quantity-of-engagement
 * field (no "follower count", "like count", "stream count") — Growza's
 * Acceptable Use Policy (LEVEL 2) rules that out structurally, so the
 * schema does not offer a column that would only make sense for a
 * service the platform refuses to sell.
 *
 * Two pricing shapes instead, selected by `pricing_model`:
 * - 'fixed': a flat package price (customer_price_minor is authoritative)
 * - 'budget_range': the customer picks a campaign budget within
 *   [min_budget_minor, max_budget_minor] and pays that budget plus a
 *   management fee (management_fee_minor) — this is what "minimum/maximum
 *   quantity or budget" in the master prompt maps to for a legitimate
 *   paid-advertising service.
 *
 * base_price_minor is Growza's own internal cost allocation (margin
 * reporting, LEVEL 17) — never shown to the customer. It is nullable
 * because not every service has a meaningfully distinct internal cost
 * yet; inventing one would be a fabricated number, not a real one.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('platform_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('service_category_id')->constrained()->cascadeOnDelete();

            $table->string('name');
            $table->string('slug')->unique();
            $table->text('summary');
            $table->longText('description')->nullable();

            $table->string('pricing_model'); // fixed | budget_range

            $table->unsignedBigInteger('min_budget_minor')->nullable();
            $table->unsignedBigInteger('max_budget_minor')->nullable();
            $table->unsignedBigInteger('management_fee_minor')->nullable();

            $table->unsignedBigInteger('base_price_minor')->nullable();
            $table->unsignedBigInteger('customer_price_minor')->nullable();

            $table->unsignedSmallInteger('estimated_delivery_min_days');
            $table->unsignedSmallInteger('estimated_delivery_max_days');

            $table->text('requirements')->nullable();
            $table->text('terms')->nullable();
            $table->text('refund_policy_note')->nullable();

            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);

            $table->timestamps();
            $table->softDeletes();

            $table->index(['is_active', 'sort_order']);
            $table->index('pricing_model');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
