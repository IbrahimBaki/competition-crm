<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_links', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('source_ticket_id')->constrained('tickets')->cascadeOnDelete();
            $table->foreignId('target_ticket_id')->constrained('tickets')->cascadeOnDelete();
            $table->string('relation', 40);
            $table->foreignId('created_by_user_id')->nullable()->constrained('users');
            $table->timestampsTz();

            $table->unique(['source_ticket_id', 'target_ticket_id', 'relation']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_links');
    }
};
