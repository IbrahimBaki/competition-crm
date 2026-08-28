<?php

namespace Database\Factories;

use App\Domains\Knowledge\Models\ArticleState;
use App\Domains\Knowledge\Models\ArticleVisibility;
use App\Domains\Knowledge\Models\KnowledgeArticle;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class KnowledgeArticleFactory extends Factory
{
    protected $model = KnowledgeArticle::class;

    public function definition(): array
    {
        $title = [
            'ar' => $this->faker->sentence(),
            'en' => $this->faker->sentence(),
        ];
        $body = [
            'ar' => $this->faker->paragraph(),
            'en' => $this->faker->paragraph(),
        ];

        return [
            'uuid' => $this->faker->uuid(),
            'knowledge_category_id' => null,
            'slug' => Str::slug($title['en']),
            'title' => $title,
            'body' => $body,
            'state' => ArticleState::Draft->value,
            'visibility' => ArticleVisibility::Public->value,
            'current_version' => 0,
            'search_ar' => $this->normaliseArabic($title['ar'].' '.$body['ar']),
            'search_en' => strtolower($title['en'].' '.$body['en']),
            'author_id' => User::factory(),
            'published_by' => null,
            'published_at' => null,
            'archived_at' => null,
            'helpful_count' => 0,
            'not_helpful_count' => 0,
        ];
    }

    public function draft(): self
    {
        return $this->state(['state' => ArticleState::Draft->value]);
    }

    public function inReview(): self
    {
        return $this->state(['state' => ArticleState::InReview->value]);
    }

    public function published(): self
    {
        return $this->state([
            'state' => ArticleState::Published->value,
            'current_version' => 1,
            'published_at' => now(),
            'published_by' => User::factory(),
        ]);
    }

    public function archived(): self
    {
        return $this->state([
            'state' => ArticleState::Archived->value,
            'archived_at' => now(),
        ]);
    }

    public function public(): self
    {
        return $this->state(['visibility' => ArticleVisibility::Public->value]);
    }

    public function customers(): self
    {
        return $this->state(['visibility' => ArticleVisibility::Customers->value]);
    }

    public function internal(): self
    {
        return $this->state(['visibility' => ArticleVisibility::Internal->value]);
    }

    private function normaliseArabic(string $text): string
    {
        $text = strtolower($text);
        $text = preg_replace('/\s+/', ' ', $text);

        return trim($text);
    }
}
