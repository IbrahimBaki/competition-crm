<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sla_breaches', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('ticket_id')->constrained('tickets')->restrictOnDelete();
            $table->foreignId('ticket_sla_clock_id')->nullable()->constrained('ticket_sla_clocks')->nullOnDelete();
            $table->string('target_type', 32);
            $table->string('priority', 32); // snapshot at breach time
            $table->unsignedInteger('target_minutes'); // snapshot
            $table->dateTime('due_at'); // snapshot target instant
            $table->dateTime('breached_at'); // actual instant
            $table->unsignedInteger('actual_minutes'); // working minutes elapsed at breach
            $table->unsignedInteger('overdue_minutes'); // working minutes past target
            $table->string('reason', 64); // SlaBreachReason value
            $table->timestamp('created_at')->useCurrent();

            $table->index(['ticket_id', 'target_type']);
        });

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::unprepared(<<<'SQL'
                CREATE TRIGGER sla_breaches_block_update BEFORE UPDATE ON sla_breaches
                FOR EACH ROW SIGNAL SQLSTATE '45000'
                  SET MESSAGE_TEXT = 'sla_breaches is append-only';
            SQL);

            DB::unprepared(<<<'SQL'
                CREATE TRIGGER sla_breaches_block_delete BEFORE DELETE ON sla_breaches
                FOR EACH ROW SIGNAL SQLSTATE '45000'
                  SET MESSAGE_TEXT = 'sla_breaches is append-only';
            SQL);
        }
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::unprepared('DROP TRIGGER IF EXISTS sla_breaches_block_update');
            DB::unprepared('DROP TRIGGER IF EXISTS sla_breaches_block_delete');
        }

        Schema::dropIfExists('sla_breaches');
    }
};
