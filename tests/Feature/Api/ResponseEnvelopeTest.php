<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResponseEnvelopeTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_list_returns_success_envelope(): void
    {
        $response = $this->getJson('/api/v1/branches');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [],
            'meta' => [
                'request_id',
                'page',
                'per_page',
                'total',
                'total_pages',
            ],
            'links' => [
                'self',
                'first',
                'prev',
                'next',
                'last',
            ],
        ]);
    }

    public function test_get_single_item_returns_success_envelope(): void
    {
        $branch = $this->seed()->factory('branch')->create();

        $response = $this->getJson('/api/v1/branches/'.$branch->id);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => ['id'],
            'meta' => ['request_id'],
        ]);
        $response->assertJsonMissing(['links']);
    }

    public function test_x_request_id_header_echoed_when_provided(): void
    {
        $requestId = 'test-request-id-12345';

        $response = $this->getJson('/api/v1/branches', [
            'X-Request-Id' => $requestId,
        ]);

        $this->assertEquals($requestId, $response->header('X-Request-Id'));
        $this->assertEquals($requestId, $response->json('meta.request_id'));
    }

    public function test_x_request_id_header_generated_when_missing(): void
    {
        $response = $this->getJson('/api/v1/branches');

        $responseId = $response->header('X-Request-Id');
        $this->assertNotNull($responseId);
        $this->assertMatchesRegularExpression('/^[0-9a-f-]{36}$/', $responseId);
        $this->assertEquals($responseId, $response->json('meta.request_id'));
    }

    public function test_every_json_response_has_request_id(): void
    {
        $responses = [
            $this->getJson('/api/v1/branches'),
            $this->getJson('/api/v1/departments'),
            $this->getJson('/api/v1/teams'),
        ];

        foreach ($responses as $response) {
            $this->assertNotNull($response->header('X-Request-Id'));
            $this->assertNotNull($response->json('meta.request_id'));
        }
    }
}
