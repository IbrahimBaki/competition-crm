<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_references', function (Blueprint $table) {
            $table->id();
            $table->string('period', 16)->unique();
            $table->unsignedBigInteger('last_sequence')->default(0);
            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_references');
    }
};
