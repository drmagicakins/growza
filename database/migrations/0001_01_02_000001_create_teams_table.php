<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Created at LEVEL 0 (empty of application UI) specifically so Agencies
 * (LEVEL 21-22) do not require a multi-tenancy retrofit onto an
 * already-populated `orders`/`users` schema. See ARCHITECTURE.md §22.
 * No agency-facing feature reads/writes this table until LEVEL 21.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('team_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            // Team-scoped role label (e.g. Agency Owner, Manager, Finance,
            // Campaign Manager, Viewer — see LEVEL 22). Kept as a plain
            // string here; enforcement lives in Policies, not this column.
            $table->string('team_role')->default('viewer');
            $table->timestamps();

            $table->unique(['team_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('team_user');
        Schema::dropIfExists('teams');
    }
};
