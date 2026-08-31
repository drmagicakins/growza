<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Foundational audit table (LEVEL 18 depends on this existing from day
 * one so early levels — auth, RBAC — can start writing audit events
 * immediately rather than bolting logging on retroactively).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();

            // Polymorphic actor: usually a User, but system/queue-triggered
            // actions (e.g. an automatic refund) may have a null actor with
            // a descriptive `actor_label` instead.
            $table->nullableMorphs('actor');
            $table->string('actor_label')->nullable();

            $table->string('action'); // e.g. 'role.changed', 'wallet.adjusted'
            $table->nullableMorphs('subject');

            $table->json('before')->nullable();
            $table->json('after')->nullable();
            $table->text('reason')->nullable();

            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();

            $table->timestamp('created_at')->useCurrent();

            $table->index('action');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
