<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * MySQL-only: Replace the audit_logs_block_delete trigger to permit deletes
     * of records older than the audit retention hard floor (365 days, baked as a literal).
     *
     * The old trigger rejected ALL deletes. This new one rejects only in-window deletes
     * while allowing bounded purges of older records. The UPDATE trigger remains untouched.
     *
     * SQLite has no trigger support; deletes are only guarded by AuditLog::booted() model events,
     * which can be bypassed by DB::table('audit_logs')->delete() as used in AuditPurgeHandler.
     *
     * The hard floor (365 days) is baked into the trigger as a literal;
     * AuditPurgeHandler::purge() enforces the same config value independently.
     */
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::unprepared('DROP TRIGGER IF EXISTS audit_logs_block_delete');

            DB::unprepared(<<<'SQL'
                CREATE TRIGGER audit_logs_block_delete BEFORE DELETE ON audit_logs
                FOR EACH ROW
                BEGIN
                  IF OLD.recorded_at > (NOW() - INTERVAL 365 DAY) THEN
                    SIGNAL SQLSTATE '45000'
                      SET MESSAGE_TEXT = 'audit_logs is append-only (records newer than 365 days cannot be deleted)';
                  END IF;
                END;
            SQL);
        }
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::unprepared('DROP TRIGGER IF EXISTS audit_logs_block_delete');

            DB::unprepared(<<<'SQL'
                CREATE TRIGGER audit_logs_block_delete BEFORE DELETE ON audit_logs
                FOR EACH ROW SIGNAL SQLSTATE '45000'
                  SET MESSAGE_TEXT = 'audit_logs is append-only';
            SQL);
        }
    }
};
