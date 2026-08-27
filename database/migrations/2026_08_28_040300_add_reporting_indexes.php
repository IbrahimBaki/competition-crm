<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            // For ticket volume reporting by date range and department
            if (! Schema::hasIndex('tickets', 'idx_tickets_created_department')) {
                $table->index(['created_at', 'department_id'], 'idx_tickets_created_department');
            }
        });

        Schema::table('ticket_events', function (Blueprint $table) {
            // For resolved/reopened event counting
            if (! Schema::hasIndex('ticket_events', 'idx_events_type_created')) {
                $table->index(['type', 'created_at'], 'idx_events_type_created');
            }
        });

        Schema::table('ticket_sla_clocks', function (Blueprint $table) {
            // For SLA performance reporting
            if (! Schema::hasIndex('ticket_sla_clocks', 'idx_sla_clocks_target_state')) {
                $table->index(['target_type', 'state', 'created_at'], 'idx_sla_clocks_target_state');
            }
        });

        Schema::table('ticket_feedback', function (Blueprint $table) {
            // For satisfaction reporting
            if (! Schema::hasIndex('ticket_feedback', 'idx_feedback_created')) {
                $table->index(['created_at'], 'idx_feedback_created');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            if (Schema::hasIndex('tickets', 'idx_tickets_created_department')) {
                $table->dropIndex('idx_tickets_created_department');
            }
        });

        Schema::table('ticket_events', function (Blueprint $table) {
            if (Schema::hasIndex('ticket_events', 'idx_events_type_created')) {
                $table->dropIndex('idx_events_type_created');
            }
        });

        Schema::table('ticket_sla_clocks', function (Blueprint $table) {
            if (Schema::hasIndex('ticket_sla_clocks', 'idx_sla_clocks_target_state')) {
                $table->dropIndex('idx_sla_clocks_target_state');
            }
        });

        Schema::table('ticket_feedback', function (Blueprint $table) {
            if (Schema::hasIndex('ticket_feedback', 'idx_feedback_created')) {
                $table->dropIndex('idx_feedback_created');
            }
        });
    }
};
