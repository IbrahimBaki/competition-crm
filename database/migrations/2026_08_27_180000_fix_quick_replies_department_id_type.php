<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quick_replies', function (Blueprint $table) {
            $table->dropForeign('quick_replies_department_id_foreign');
            $table->dropColumn('department_id');
        });

        Schema::table('quick_replies', function (Blueprint $table) {
            $table->char('department_id', 36)->nullable()->after('owner_id');
            $table->foreign('department_id')->references('id')->on('departments')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('quick_replies', function (Blueprint $table) {
            $table->dropForeign('quick_replies_department_id_foreign');
            $table->dropColumn('department_id');
        });

        Schema::table('quick_replies', function (Blueprint $table) {
            $table->foreignId('department_id')->nullable();
            $table->foreign('department_id')->references('id')->on('departments')->nullOnDelete();
        });
    }
};
