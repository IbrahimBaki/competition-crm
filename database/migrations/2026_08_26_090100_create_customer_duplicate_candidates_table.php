<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_duplicate_candidates', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('duplicate_customer_id')->constrained('customers')->cascadeOnDelete();
            $table->string('status', 20)->default('pending');
            $table->string('rule', 40);
            $table->json('evidence');
            $table->foreignId('reviewed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestampTz('reviewed_at')->nullable();
            $table->timestampsTz();

            $table->unique(['customer_id', 'duplicate_customer_id'], 'dup_cand_pair_unique');
            $table->index(['status', 'created_at'], 'dup_cand_status_created');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_duplicate_candidates');
    }
};
