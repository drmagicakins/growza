<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Inbound enquiries from the public marketing contact form (LEVEL 2).
 *
 * Deliberately separate from `support_tickets` (LEVEL 13): those belong to
 * an authenticated user and have an assignment/SLA workflow. These come
 * from anonymous visitors who may never register. LEVEL 13 may add a
 * nullable `converted_to_ticket_id` here — it is NOT added now, because
 * that table does not exist yet and building ahead of a level is against
 * the project's own development rules.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_messages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('subject')->nullable();
            $table->text('message');

            // Triage state for whoever works the inbox (admin module, LEVEL 16).
            $table->string('status')->default('new'); // new | read | replied | archived
            $table->timestamp('handled_at')->nullable();

            // Kept for abuse investigation and rate-limit forensics only.
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();

            $table->timestamps();

            $table->index('status');
            $table->index('created_at');
            $table->index('email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_messages');
    }
};
