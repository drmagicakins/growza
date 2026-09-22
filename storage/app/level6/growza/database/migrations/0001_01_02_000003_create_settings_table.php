<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Key-value application settings (admin "Settings" module, LEVEL 16),
 * scaffolded at LEVEL 0 since referral commission rates (LEVEL 14),
 * coupon global rules (LEVEL 15), and provider health-check intervals
 * (LEVEL 10) all read from here rather than hard-coded config.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('group')->default('general'); // general | payments | referrals | providers ...
            $table->string('key');
            $table->json('value')->nullable();
            $table->boolean('is_encrypted')->default(false);
            $table->timestamps();

            $table->unique(['group', 'key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
