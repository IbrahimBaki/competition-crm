<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('chat_session_id')->constrained('chat_sessions')->cascadeOnDelete();
            $table->unsignedInteger('sequence');
            $table->string('author_type');
            $table->uuid('author_user_id')->nullable();
            $table->foreign('author_user_id')->references('uuid')->on('users')->nullOnDelete();
            $table->text('body');
            $table->timestamp('sent_at');
            $table->foreignId('ticket_message_id')->nullable()->constrained('ticket_messages')->nullOnDelete();
            $table->string('client_message_id')->nullable();
            $table->timestamps();
            $table->unique(['chat_session_id', 'sequence']);
            $table->unique(['chat_session_id', 'client_message_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_messages');
    }
};
