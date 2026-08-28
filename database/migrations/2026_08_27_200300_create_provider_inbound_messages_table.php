<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('provider_inbound_messages', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            $table->string('channel', 20)->notNullable();
            $table->string('provider_message_id', 191)->notNullable();
            $table->string('from_identifier', 320)->notNullable();

            $table->json('raw_payload')->notNullable();
            $table->string('state', 32)->notNullable()->default('received');

            $table->foreignId('ticket_id')->nullable()->constrained('tickets')->nullOnDelete();
            $table->timestamp('received_at')->notNullable();

            $table->timestamps();

            $table->unique(['channel', 'provider_message_id']);
            $table->index(['state', 'received_at']);
            $table->index(['channel', 'from_identifier']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('provider_inbound_messages');
    }
};
