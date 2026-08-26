<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add UUID column to users table.
     *
     * Note: We keep the auto-increment `id` column because Sanctum's
     * personal_access_tokens.tokenable_id is bigint. A follow-up story
     * will flip the PKs after Sanctum migration is handled.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->char('uuid', 36)->unique()->nullable()->after('id');
        });

        // Backfill existing rows with UUIDs
        DB::table('users')->whereNull('uuid')->update([
            'uuid' => DB::raw('UUID()'),
        ]);

        // Verify all rows have UUIDs
        $nullCount = DB::table('users')->whereNull('uuid')->count();
        if ($nullCount > 0) {
            throw new RuntimeException("Failed to backfill UUIDs: {$nullCount} rows remain null");
        }

        // Make NOT NULL after backfill
        Schema::table('users', function (Blueprint $table) {
            $table->char('uuid', 36)->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });
    }
};
