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
            if (! $this->hasIndex('tickets', 'idx_tickets_created_department')) {
                $table->index(['created_at', 'department_id'], 'idx_tickets_created_department');
            }
        });

        Schema::table('ticket_events', function (Blueprint $table) {
            // For resolved/reopened event counting
            if (! $this->hasIndex('ticket_events', 'idx_events_type_created')) {
                $table->index(['type', 'created_at'], 'idx_events_type_created');
            }
        });

        Schema::table('ticket_sla_clocks', function (Blueprint $table) {
            // For SLA performance reporting
            if (! $this->hasIndex('ticket_sla_clocks', 'idx_sla_clocks_target_state')) {
                $table->index(['target_type', 'state', 'created_at'], 'idx_sla_clocks_target_state');
            }
        });

        Schema::table('ticket_feedback', function (Blueprint $table) {
            // For satisfaction reporting
            if (! $this->hasIndex('ticket_feedback', 'idx_feedback_created')) {
                $table->index(['created_at'], 'idx_feedback_created');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropIndexIfExists('idx_tickets_created_department');
        });

        Schema::table('ticket_events', function (Blueprint $table) {
            $table->dropIndexIfExists('idx_events_type_created');
        });

        Schema::table('ticket_sla_clocks', function (Blueprint $table) {
            $table->dropIndexIfExists('idx_sla_clocks_target_state');
        });

        Schema::table('ticket_feedback', function (Blueprint $table) {
            $table->dropIndexIfExists('idx_feedback_created');
        });
    }

    private function hasIndex(string $table, string $indexName): bool
    {
        $indexes = Schema::getConnection()->getDoctrineSchemaManager()->listTableIndexes($table);

        return isset($indexes[strtolower($indexName)]);
    }
};
