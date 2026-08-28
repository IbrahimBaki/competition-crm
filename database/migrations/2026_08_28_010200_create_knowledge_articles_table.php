<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('knowledge_articles', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('knowledge_category_id')->nullable()->constrained('knowledge_categories')->nullOnDelete();
            $table->string('slug')->unique();
            $table->json('title');
            $table->json('body');
            $table->string('state', 32)->index();
            $table->string('visibility', 32)->index();
            $table->unsignedInteger('current_version')->default(0);
            $table->text('search_ar')->nullable();
            $table->text('search_en')->nullable();
            $table->foreignId('author_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('published_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('published_at')->nullable();
            $table->timestamp('archived_at')->nullable();
            $table->unsignedInteger('helpful_count')->default(0);
            $table->unsignedInteger('not_helpful_count')->default(0);
            $table->timestamps();

            $table->index(['visibility', 'state']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('knowledge_articles');
    }
};
