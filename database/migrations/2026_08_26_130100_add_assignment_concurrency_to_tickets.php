<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->unsignedInteger('version')->default(1)->after('spam_marked_at');
            $table->timestamp('assigned_at')->nullable()->after('version');
            $table->timestamp('assignment_locked_at')->nullable()->after('assigned_at');

            $table->index(['department_id', 'assigned_user_id']);
            if (! Schema::hasColumn('tickets', 'assigned_user_id')) {
                $table->unsignedBigInteger('assigned_user_id')->nullable();
            }
            $table->index('assigned_user_id');
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropIndex(['department_id', 'assigned_user_id']);
            $table->dropIndex(['assigned_user_id']);
            $table->dropColumn(['version', 'assigned_at', 'assignment_locked_at']);
        });
    }
};
