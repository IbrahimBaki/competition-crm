<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sla_policies', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->json('name');
            $table->char('branch_id', 36);
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedTinyInteger('warning_threshold_percent')->default(80);
            $table->timestampsTz();

            $table->foreign('branch_id')->references('id')->on('branches')->restrictOnDelete();
            $table->index(['branch_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sla_policies');
    }
};
