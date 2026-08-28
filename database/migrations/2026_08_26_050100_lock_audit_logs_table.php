<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * MySQL-only triggers and indexes to enforce audit-log immutability.
     * SQLite has no trigger support; the model-layer guard via AuditLogImmutableException
     * covers the Eloquent path.
     */
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::unprepared(<<<'SQL'
                CREATE TRIGGER audit_logs_block_update BEFORE UPDATE ON audit_logs
                FOR EACH ROW SIGNAL SQLSTATE '45000'
                  SET MESSAGE_TEXT = 'audit_logs is append-only';
            SQL);

            DB::unprepared(<<<'SQL'
                CREATE TRIGGER audit_logs_block_delete BEFORE DELETE ON audit_logs
                FOR EACH ROW SIGNAL SQLSTATE '45000'
                  SET MESSAGE_TEXT = 'audit_logs is append-only';
            SQL);
        }

        // Add indexes to accelerate list endpoint filters
        Schema::table('audit_logs', function ($table) {
            $table->index(['action', 'recorded_at']);
            $table->index(['recorded_at']);
        });
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::unprepared('DROP TRIGGER IF EXISTS audit_logs_block_update');
            DB::unprepared('DROP TRIGGER IF EXISTS audit_logs_block_delete');
        }

        Schema::table('audit_logs', function ($table) {
            $table->dropIndex(['action', 'recorded_at']);
            $table->dropIndex(['recorded_at']);
        });
    }
};
