<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_sla_clocks', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('ticket_id')->constrained('tickets')->cascadeOnDelete();
            $table->foreignId('sla_policy_id')->nullable()->constrained('sla_policies')->nullOnDelete();
            $table->foreignId('sla_target_id')->nullable()->constrained('sla_targets')->nullOnDelete();
            $table->string('target_type', 32); // 'first_response' | 'resolution'
            $table->unsignedInteger('target_minutes'); // current target (working minutes)
            $table->dateTime('started_at'); // clock origin (UTC)
            $table->unsignedInteger('elapsed_minutes')->default(0); // frozen working minutes up to last_counted_at
            $table->dateTime('last_counted_at'); // watermark: elapsed_minutes is exact up to here
            $table->dateTime('due_at')->nullable(); // computed target instant (UTC)
            $table->string('state', 32); // SlaClockState value
            $table->dateTime('paused_at')->nullable();
            $table->dateTime('warned_at')->nullable();
            $table->dateTime('completed_at')->nullable(); // first response sent / ticket resolved
            $table->dateTime('breached_at')->nullable();
            $table->dateTime('reset_at')->nullable();
            $table->timestampsTz();

            $table->unique(['ticket_id', 'target_type']);
            $table->index(['state', 'due_at']); // sweep command driver
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_sla_clocks');
    }
};
