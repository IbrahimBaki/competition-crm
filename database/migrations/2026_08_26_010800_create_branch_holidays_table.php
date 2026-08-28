<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create branch holidays table.
     * Each holiday is either a one-off date (YYYY-MM-DD) or a recurring annual date (MM-DD).
     * Exactly one must be set; enforced in the FormRequest.
     */
    public function up(): void
    {
        Schema::create('branch_holidays', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->char('branch_id', 36);
            $table->json('name');
            $table->date('date')->nullable();
            $table->char('recurring_month_day', 5)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('branch_id')->references('id')->on('branches')->cascadeOnDelete();
            $table->index(['branch_id', 'date']);
            $table->index(['branch_id', 'recurring_month_day']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('branch_holidays');
    }
};
