<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chatbot_turns', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();
            $table->foreignId('chat_session_id')->constrained('chat_sessions')->cascadeOnDelete();
            $table->foreignId('visitor_message_id')->nullable()->constrained('chat_messages')->nullOnDelete();
            $table->foreignId('bot_message_id')->nullable()->constrained('chat_messages')->nullOnDelete();
            $table->boolean('answered');
            $table->foreignId('grounded_article_id')->nullable()->constrained('knowledge_articles')->nullOnDelete();
            $table->decimal('confidence', 4, 3)->nullable();
            $table->unsignedTinyInteger('failure_streak')->default(0);
            $table->boolean('handoff_triggered')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chatbot_turns');
    }
};
