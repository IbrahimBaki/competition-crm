<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('webhook_deliveries', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('webhook_subscription_id')->constrained('webhook_subscriptions')->cascadeOnDelete();
            $table->string('event_type', 40)->index();
            $table->string('event_uuid')->index();
            $table->json('payload');
            $table->string('state', 20); // pending, delivering, delivered, failed
            $table->unsignedInteger('attempt_count')->default(0);
            $table->unsignedSmallInteger('last_status_code')->nullable();
            $table->text('last_error')->nullable();
            $table->timestamp('next_attempt_at')->nullable()->index();
            $table->timestamp('delivered_at')->nullable();
            $table->timestampsTz();
            $table->unique(['webhook_subscription_id', 'event_uuid']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('webhook_deliveries');
    }
};
