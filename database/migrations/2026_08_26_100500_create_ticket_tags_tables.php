<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_tags', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name', 64);
            $table->string('name_normalised', 64)->unique();
            $table->timestampsTz();
        });

        Schema::create('ticket_tag_ticket', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained('tickets')->cascadeOnDelete();
            $table->foreignId('ticket_tag_id')->constrained('ticket_tags')->cascadeOnDelete();
            $table->timestampsTz();

            $table->unique(['ticket_id', 'ticket_tag_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_tag_ticket');
        Schema::dropIfExists('ticket_tags');
    }
};
