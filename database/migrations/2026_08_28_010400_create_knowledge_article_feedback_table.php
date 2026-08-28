<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('knowledge_article_feedback', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('knowledge_article_id')->constrained('knowledge_articles')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('visitor_key')->nullable();
            $table->boolean('is_helpful');
            $table->timestamps();

            $table->unique(['knowledge_article_id', 'user_id'], 'idx_feedback_user');
            $table->unique(['knowledge_article_id', 'visitor_key'], 'idx_feedback_visitor');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('knowledge_article_feedback');
    }
};
