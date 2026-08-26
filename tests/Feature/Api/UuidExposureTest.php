<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UuidExposureTest extends TestCase
{
    use RefreshDatabase;

    public function test_list_response_ids_are_uuid(): void
    {
        $response = $this->getJson('/api/v1/branches');

        $response->assertStatus(200);
        $items = $response->json('data');

        foreach ($items as $item) {
            $this->assertIsString($item['id']);
            $this->assertMatchesRegularExpression('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $item['id']);
        }
    }

    public function test_single_item_id_is_uuid(): void
    {
        $branch = $this->seed()->factory('branch')->create();

        $response = $this->getJson('/api/v1/branches/'.$branch->id);

        $response->assertStatus(200);
        $this->assertMatchesRegularExpression('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $response->json('data.id'));
    }

    public function test_timestamps_are_iso8601_with_offset(): void
    {
        $response = $this->getJson('/api/v1/branches');

        $response->assertStatus(200);
        $items = $response->json('data');

        foreach ($items as $item) {
            if (isset($item['created_at'])) {
                $this->assertMatchesRegularExpression('/T.*(\+|-)\d{2}:\d{2}$/', $item['created_at']);
            }
            if (isset($item['updated_at'])) {
                $this->assertMatchesRegularExpression('/T.*(\+|-)\d{2}:\d{2}$/', $item['updated_at']);
            }
        }
    }

    public function test_single_item_timestamps_are_iso8601(): void
    {
        $branch = $this->seed()->factory('branch')->create();

        $response = $this->getJson('/api/v1/branches/'.$branch->id);

        $response->assertStatus(200);
        $item = $response->json('data');

        if (isset($item['created_at'])) {
            $this->assertMatchesRegularExpression('/T.*(\+|-)\d{2}:\d{2}$/', $item['created_at']);
        }
    }

    public function test_no_numeric_ids_in_responses(): void
    {
        $response = $this->getJson('/api/v1/branches');

        $response->assertStatus(200);
        $items = $response->json('data');

        foreach ($items as $item) {
            $this->assertIsString($item['id']);
            $this->assertFalse(is_numeric($item['id']) && strpos($item['id'], '-') === false);
        }
    }
}
