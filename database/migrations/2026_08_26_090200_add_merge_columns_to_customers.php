<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->foreignId('merged_into_customer_id')->nullable()->after('id')
                ->constrained('customers')->nullOnDelete();
            $table->timestampTz('merged_at')->nullable();
            $table->index('merged_into_customer_id');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropConstrainedForeignId('merged_into_customer_id');
            $table->dropColumn('merged_at');
        });
    }
};
