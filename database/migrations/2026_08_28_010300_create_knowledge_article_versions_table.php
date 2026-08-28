<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('knowledge_article_versions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('knowledge_article_id')->constrained('knowledge_articles')->cascadeOnDelete();
            $table->unsignedInteger('version');
            $table->json('title');
            $table->json('body');
            $table->string('visibility', 32);
            $table->foreignId('knowledge_category_id')->nullable()->constrained('knowledge_categories')->nullOnDelete();
            $table->foreignId('published_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('published_at');
            $table->timestamps();

            $table->unique(['knowledge_article_id', 'version']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('knowledge_article_versions');
    }
};
