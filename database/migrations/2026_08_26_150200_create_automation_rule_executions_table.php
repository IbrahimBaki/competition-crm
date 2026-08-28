<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('automation_rule_executions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('automation_rule_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ticket_id')->constrained()->cascadeOnDelete();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('trigger');
            $table->string('outcome');
            $table->json('condition_snapshot');
            $table->json('changes');
            $table->text('reason')->nullable();
            $table->string('idempotency_key')->unique();
            $table->timestamp('executed_at');
            $table->timestamps();

            $table->index(['ticket_id', 'executed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('automation_rule_executions');
    }
};
