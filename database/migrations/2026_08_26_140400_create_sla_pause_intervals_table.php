<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sla_pause_intervals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_sla_clock_id')->constrained('ticket_sla_clocks')->cascadeOnDelete();
            $table->foreignId('ticket_status_id')->nullable()->constrained('ticket_statuses');
            $table->dateTime('paused_at');
            $table->dateTime('resumed_at')->nullable();
            $table->unsignedInteger('paused_working_minutes')->nullable(); // filled on resume
            $table->text('reason')->nullable();
            $table->timestampsTz();

            $table->index(['ticket_sla_clock_id', 'resumed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sla_pause_intervals');
    }
};
