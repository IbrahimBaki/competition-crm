<?php

namespace Database\Factories;

use App\Domains\Knowledge\Models\ArticleVisibility;
use App\Domains\Knowledge\Models\KnowledgeArticleVersion;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class KnowledgeArticleVersionFactory extends Factory
{
    protected $model = KnowledgeArticleVersion::class;

    public function definition(): array
    {
        return [
            'uuid' => $this->faker->uuid(),
            'knowledge_article_id' => null,
            'version' => 1,
            'title' => [
                'ar' => $this->faker->sentence(),
                'en' => $this->faker->sentence(),
            ],
            'body' => [
                'ar' => $this->faker->paragraph(),
                'en' => $this->faker->paragraph(),
            ],
            'visibility' => ArticleVisibility::Public->value,
            'knowledge_category_id' => null,
            'published_by' => User::factory(),
            'published_at' => now(),
        ];
    }
}
