<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Captures the referral code a user typed at registration (LEVEL 3).
 *
 * Stored as a plain string with NO foreign key, deliberately: the
 * `referral_codes` table does not exist until LEVEL 14, and creating a
 * constraint against a table that has not been designed yet would be
 * building ahead of the level that owns it.
 *
 * LEVEL 14 resolves this string into a `referral_conversions` row. Until
 * then the value is recorded but has no financial effect — no commission
 * is calculated, because the commission engine does not exist.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('referred_by_code')->nullable()->after('phone');
            $table->index('referred_by_code');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['referred_by_code']);
            $table->dropColumn('referred_by_code');
        });
    }
};
