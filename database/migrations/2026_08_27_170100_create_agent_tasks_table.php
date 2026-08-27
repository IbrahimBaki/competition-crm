<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agent_tasks', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();
            $table->foreignId('owner_id')->constrained('users');
            $table->foreignId('created_by_id')->constrained('users');
            $table->foreignId('ticket_id')->nullable()->constrained('tickets');
            $table->string('title');
            $table->text('description')->nullable();
            $table->dateTime('due_at')->nullable();
            $table->boolean('due_in_working_time')->default(false);
            $table->char('branch_id', 36)->nullable();
            $table->foreign('branch_id')->references('id')->on('branches')->nullOnDelete();
            $table->string('state'); // enum: open, in_progress, done, cancelled
            $table->dateTime('completed_at')->nullable();
            $table->dateTime('cancelled_at')->nullable();
            $table->dateTime('reminder_at')->nullable();
            $table->dateTime('reminder_sent_at')->nullable();
            $table->timestamps();

            $table->index(['owner_id', 'state', 'due_at']);
            $table->index('ticket_id');
            $table->index(['reminder_at', 'reminder_sent_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agent_tasks');
    }
};
