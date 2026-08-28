<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CollectionQueryTest extends TestCase
{
    use RefreshDatabase;

    public function test_collection_respects_page_parameter(): void
    {
        $response = $this->getJson('/api/v1/branches?page=1');

        $response->assertStatus(200);
        $this->assertEquals(1, $response->json('meta.page'));
    }

    public function test_collection_respects_per_page_default(): void
    {
        $response = $this->getJson('/api/v1/branches');

        $response->assertStatus(200);
        $this->assertEquals(25, $response->json('meta.per_page'));
    }

    public function test_collection_respects_per_page_custom(): void
    {
        $response = $this->getJson('/api/v1/branches?per_page=50');

        $response->assertStatus(200);
        $this->assertEquals(50, $response->json('meta.per_page'));
    }

    public function test_collection_rejects_per_page_above_max(): void
    {
        $response = $this->getJson('/api/v1/branches?per_page=101');

        $response->assertStatus(422);
        $this->assertEquals('validation_failed', $response->json('error.code'));
    }

    public function test_collection_rejects_per_page_zero(): void
    {
        $response = $this->getJson('/api/v1/branches?per_page=0');

        $response->assertStatus(422);
        $this->assertEquals('validation_failed', $response->json('error.code'));
    }

    public function test_collection_includes_pagination_links(): void
    {
        $response = $this->getJson('/api/v1/branches?page=1&per_page=10');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'links' => [
                'self',
                'first',
                'prev',
                'next',
                'last',
            ],
        ]);
    }

    public function test_collection_includes_total_and_total_pages(): void
    {
        $response = $this->getJson('/api/v1/branches?per_page=10');

        $response->assertStatus(200);
        $this->assertIsInt($response->json('meta.total'));
        $this->assertIsInt($response->json('meta.total_pages'));
    }

    public function test_collection_respects_sort_parameter(): void
    {
        $response = $this->getJson('/api/v1/branches?sort=-created_at');

        $response->assertStatus(200);
        $this->assertEquals('-created_at', $response->json('meta.sort'));
    }

    public function test_collection_respects_filter_in_meta(): void
    {
        $response = $this->getJson('/api/v1/branches?filter[is_active][eq]=true');

        $response->assertStatus(200);
        $this->assertNotEmpty($response->json('meta.filters'));
    }

    public function test_collection_returns_array_of_items(): void
    {
        $response = $this->getJson('/api/v1/branches');

        $response->assertStatus(200);
        $this->assertIsArray($response->json('data'));
    }
}
