<?php

namespace Tests\Feature\Knowledge;

use App\Models\User;
use Database\Factories\KnowledgeArticleFactory;
use Tests\TestCase;

class KnowledgeFeedbackTest extends TestCase
{
    public function test_feedback_increments_helpful_count(): void
    {
        $user = User::factory()->create();
        $article = KnowledgeArticleFactory::new()->published()->create();

        $this->actingAs($user)
            ->postJson("/api/v1/public/knowledge/articles/{$article->uuid}/feedback", [
                'is_helpful' => true,
            ]);

        $article->refresh();
        $this->assertEquals(1, $article->helpful_count);
        $this->assertEquals(0, $article->not_helpful_count);
    }

    public function test_repeat_vote_with_same_value_returns_409(): void
    {
        $user = User::factory()->create();
        $article = KnowledgeArticleFactory::new()->published()->create();

        $this->actingAs($user)
            ->postJson("/api/v1/public/knowledge/articles/{$article->uuid}/feedback", [
                'is_helpful' => true,
            ]);

        $response = $this->actingAs($user)
            ->postJson("/api/v1/public/knowledge/articles/{$article->uuid}/feedback", [
                'is_helpful' => true,
            ]);

        $this->assertEquals(409, $response->status());
    }

    public function test_opposite_vote_flips_and_adjusts_counters(): void
    {
        $user = User::factory()->create();
        $article = KnowledgeArticleFactory::new()->published()->create();

        $this->actingAs($user)
            ->postJson("/api/v1/public/knowledge/articles/{$article->uuid}/feedback", [
                'is_helpful' => true,
            ]);

        $article->refresh();
        $this->assertEquals(1, $article->helpful_count);

        $this->actingAs($user)
            ->postJson("/api/v1/public/knowledge/articles/{$article->uuid}/feedback", [
                'is_helpful' => false,
            ]);

        $article->refresh();
        $this->assertEquals(0, $article->helpful_count);
        $this->assertEquals(1, $article->not_helpful_count);
    }

    public function test_anonymous_feedback_without_visitor_key_returns_422(): void
    {
        $article = KnowledgeArticleFactory::new()->published()->create();

        $response = $this->postJson("/api/v1/public/knowledge/articles/{$article->uuid}/feedback", [
            'is_helpful' => true,
        ]);

        $this->assertEquals(422, $response->status());
    }

    public function test_anonymous_feedback_with_visitor_key(): void
    {
        $article = KnowledgeArticleFactory::new()->published()->create();

        $response = $this->postJson("/api/v1/public/knowledge/articles/{$article->uuid}/feedback", [
            'is_helpful' => true,
            'visitor_key' => 'visitor-123',
        ]);

        $this->assertEquals(201, $response->status());
        $article->refresh();
        $this->assertEquals(1, $article->helpful_count);
    }
}
