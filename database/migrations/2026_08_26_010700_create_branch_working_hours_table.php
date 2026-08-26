<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create branch working hours table.
     * Stores weekly pattern per day (0=Sunday..6=Saturday).
     * Overnight shifts (closes_at <= opens_at) are not supported; enforce in the Request layer.
     */
    public function up(): void
    {
        Schema::create('branch_working_hours', function (Blueprint $table) {
            $table->id();
            $table->char('branch_id', 36);
            $table->unsignedTinyInteger('day_of_week');
            $table->boolean('is_working')->default(false);
            $table->time('opens_at')->nullable();
            $table->time('closes_at')->nullable();
            $table->timestamps();

            $table->unique(['branch_id', 'day_of_week']);
            $table->foreign('branch_id')->references('id')->on('branches')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('branch_working_hours');
    }
};
