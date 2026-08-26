<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_category_fields', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('ticket_category_id')->constrained('ticket_categories')->cascadeOnDelete();
            $table->string('key', 64);
            $table->json('label');
            $table->string('type', 24);
            $table->json('options')->nullable();
            $table->boolean('is_required')->default(false);
            $table->unsignedInteger('position')->default(0);
            $table->timestampsTz();

            $table->unique(['ticket_category_id', 'key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_category_fields');
    }
};
