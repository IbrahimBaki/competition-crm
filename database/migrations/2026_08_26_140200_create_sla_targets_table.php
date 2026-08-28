<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sla_targets', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('sla_policy_id')->constrained('sla_policies')->cascadeOnDelete();
            $table->string('target_type', 32); // 'first_response' | 'resolution'
            $table->string('priority', 32); // TicketPriority enum value
            $table->foreignId('ticket_category_id')->nullable()->constrained('ticket_categories')->nullOnDelete();
            $table->string('service_tier', 32)->nullable(); // Customers ServiceTier enum value
            $table->unsignedInteger('minutes'); // WORKING minutes
            $table->timestampsTz();

            $table->unique(
                ['sla_policy_id', 'target_type', 'priority', 'ticket_category_id', 'service_tier'],
                'sla_targets_match_unique'
            );
            $table->index(['sla_policy_id', 'target_type', 'priority']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sla_targets');
    }
};
