<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_contacts', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->string('type', 20);
            $table->string('value', 255);
            $table->string('value_normalised', 191)->index();
            $table->string('label', 100)->nullable();
            $table->boolean('is_primary')->default(false);
            $table->timestampTz('verified_at')->nullable();
            $table->timestampsTz();

            $table->unique(['type', 'value_normalised']);
            $table->index(['customer_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_contacts');
    }
};
