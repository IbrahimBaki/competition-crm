<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inbound_email_messages', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('provider', 64);
            $table->string('provider_event_id', 191)->nullable();
            $table->string('message_id', 191)->nullable()->index();
            $table->string('from_address', 320);
            $table->string('to_address', 320)->nullable();
            $table->string('subject', 512)->nullable();
            $table->string('raw_path', 512);              // storage path of raw MIME
            $table->string('classification', 32);          // reply|new|auto_reply|bounce|loop
            $table->string('state', 32);                   // received|processed|suppressed|failed
            $table->unsignedInteger('attempts')->default(0);
            $table->text('last_error')->nullable();
            $table->foreignId('ticket_id')->nullable()->constrained('tickets')->nullOnDelete();
            $table->foreignId('ticket_message_id')->nullable()->constrained('ticket_messages')->nullOnDelete();
            $table->timestamp('received_at');
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
            $table->unique(['provider', 'provider_event_id']);
            $table->index(['state', 'received_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inbound_email_messages');
    }
};
