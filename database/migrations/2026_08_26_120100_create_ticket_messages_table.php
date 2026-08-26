<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_messages', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid()->unique();

            $table->foreignId('ticket_id')->constrained('tickets')->cascadeOnDelete();

            $table->string('direction', 16)->notNullable();
            $table->string('author_type', 16)->notNullable();
            $table->foreignId('author_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('author_customer_contact_id')->nullable()->constrained('customer_contacts')->nullOnDelete();

            $table->string('channel', 20)->notNullable();
            $table->boolean('is_internal')->default(false)->notNullable();

            $table->text('body')->notNullable();
            $table->string('body_format', 10)->default('text');

            $table->string('delivery_state', 16)->nullable();
            $table->string('failure_reason', 64)->nullable();
            $table->text('failure_detail')->nullable();

            $table->timestamp('failed_at')->nullable();
            $table->timestamp('queued_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('read_at')->nullable();

            $table->string('external_message_id', 191)->nullable();
            $table->unsignedSmallInteger('retry_count')->default(0);

            $table->timestamp('redacted_at')->nullable();

            $table->timestamps();

            $table->index(['ticket_id', 'created_at']);
            $table->index(['ticket_id', 'is_internal']);
            $table->index(['delivery_state', 'failed_at']);
            // Note: unique constraint on [channel, external_message_id] enforced in application layer
            // due to conditional uniqueness requirement (only when external_message_id is not null).
            // Create plain (non-unique) index for query performance.
            $table->index(['channel', 'external_message_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_message_delivery_events');
        Schema::dropIfExists('ticket_messages');
    }
};
