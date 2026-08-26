<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_events', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->string('type', 40);
            $table->foreignId('actor_user_id')->nullable()->constrained('users');
            $table->json('payload')->nullable();
            $table->timestampTz('occurred_at')->index();
            $table->timestampsTz();

            $table->index(['customer_id', 'occurred_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_events');
    }
};
