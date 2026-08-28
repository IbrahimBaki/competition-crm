<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_sessions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('chat_visitor_identity_id')->constrained('chat_visitor_identities')->cascadeOnDelete();
            $table->foreignId('ticket_id')->nullable()->constrained('tickets')->nullOnDelete();
            $table->uuid('branch_id')->nullable();
            $table->foreign('branch_id')->references('id')->on('branches')->nullOnDelete();
            $table->uuid('department_id')->nullable();
            $table->foreign('department_id')->references('id')->on('departments')->nullOnDelete();
            $table->uuid('assigned_user_id')->nullable()->constrained('users', 'uuid')->nullOnDelete();
            $table->string('state')->index();
            $table->string('handled_by')->nullable();
            $table->string('subject')->nullable();
            $table->text('initial_message')->nullable();
            $table->timestamp('requested_at')->nullable();
            $table->timestamp('queued_at')->nullable();
            $table->timestamp('activated_at')->nullable();
            $table->timestamp('transferred_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->timestamp('abandoned_at')->nullable();
            $table->timestamp('transcript_persisted_at')->nullable();
            $table->timestamp('last_visitor_seen_at')->nullable();
            $table->timestamp('last_agent_seen_at')->nullable();
            $table->timestamp('disconnected_at')->nullable();
            $table->unsignedInteger('queue_position')->nullable();
            $table->string('end_reason')->nullable();
            $table->timestamps();
            $table->index(['state', 'queued_at']);
            $table->index(['state', 'last_visitor_seen_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_sessions');
    }
};
