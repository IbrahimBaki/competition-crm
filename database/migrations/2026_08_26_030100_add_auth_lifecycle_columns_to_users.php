<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('deactivated_at')->nullable()->after('remember_token');
            $table->char('deactivated_by_uuid', 36)->nullable()->after('deactivated_at');
            $table->timestamp('last_login_at')->nullable()->after('deactivated_by_uuid');
            $table->unsignedSmallInteger('failed_login_count')->default(0)->after('last_login_at');
            $table->timestamp('locked_until')->nullable()->after('failed_login_count');
            $table->string('two_factor_secret')->nullable()->after('locked_until');
            $table->timestamp('two_factor_confirmed_at')->nullable()->after('two_factor_secret');
            $table->json('two_factor_recovery_codes')->nullable()->after('two_factor_confirmed_at');
            $table->index('deactivated_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['deactivated_at']);
            $table->dropColumn([
                'deactivated_at',
                'deactivated_by_uuid',
                'last_login_at',
                'failed_login_count',
                'locked_until',
                'two_factor_secret',
                'two_factor_confirmed_at',
                'two_factor_recovery_codes',
            ]);
        });
    }
};
