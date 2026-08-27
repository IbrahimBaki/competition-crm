<?php

namespace Tests\Feature\Knowledge;

use App\Models\User;
use Database\Factories\KnowledgeArticleFactory;
use Tests\TestCase;

class KnowledgeVisibilityTest extends TestCase
{
    public function test_internal_article_returns_404_on_public_route_for_anonymous(): void
    {
        $article = KnowledgeArticleFactory::new()
            ->internal()
            ->published()
            ->create();

        $response = $this->getJson("/api/v1/public/knowledge/articles/{$article->uuid}");

        $this->assertEquals(404, $response->status());
    }

    public function test_internal_article_returns_404_on_public_route_for_customer(): void
    {
        $customer = User::factory()->create();
        $article = KnowledgeArticleFactory::new()
            ->internal()
            ->published()
            ->create();

        $response = $this->actingAs($customer)->getJson("/api/v1/public/knowledge/articles/{$article->uuid}");

        $this->assertEquals(404, $response->status());
    }

    public function test_customers_visibility_article_returns_404_for_anonymous(): void
    {
        $article = KnowledgeArticleFactory::new()
            ->customers()
            ->published()
            ->create();

        $response = $this->getJson("/api/v1/public/knowledge/articles/{$article->uuid}");

        $this->assertEquals(404, $response->status());
    }

    public function test_customers_visibility_article_accessible_for_authenticated_customer(): void
    {
        $customer = User::factory()->create();
        $article = KnowledgeArticleFactory::new()
            ->customers()
            ->published()
            ->create();

        $response = $this->actingAs($customer)->getJson("/api/v1/public/knowledge/articles/{$article->uuid}");

        $this->assertEquals(200, $response->status());
        $this->assertEquals($article->uuid, $response->json('data.id'));
    }

    public function test_public_article_accessible_for_anonymous(): void
    {
        $article = KnowledgeArticleFactory::new()
            ->public()
            ->published()
            ->create();

        $response = $this->getJson("/api/v1/public/knowledge/articles/{$article->uuid}");

        $this->assertEquals(200, $response->status());
        $this->assertEquals($article->uuid, $response->json('data.id'));
    }

    public function test_internal_article_not_in_public_index(): void
    {
        KnowledgeArticleFactory::new()
            ->internal()
            ->published()
            ->create();

        $response = $this->getJson('/api/v1/public/knowledge/articles');

        $this->assertEquals(200, $response->status());
        $this->assertEmpty($response->json('data'));
    }

    public function test_internal_article_not_in_public_search(): void
    {
        $article = KnowledgeArticleFactory::new()
            ->internal()
            ->published()
            ->create(['title' => ['ar' => 'اختبار', 'en' => 'test']]);

        $response = $this->getJson('/api/v1/public/knowledge/articles/search?filter[q]=test');

        $this->assertEquals(200, $response->status());
        $this->assertEmpty($response->json('data'));
    }

    public function test_public_resource_never_exposes_state(): void
    {
        $article = KnowledgeArticleFactory::new()
            ->public()
            ->published()
            ->create();

        $response = $this->getJson("/api/v1/public/knowledge/articles/{$article->uuid}");

        $this->assertEquals(200, $response->status());
        $this->assertArrayNotHasKey('state', $response->json('data'));
        $this->assertArrayNotHasKey('author', $response->json('data'));
    }
}
