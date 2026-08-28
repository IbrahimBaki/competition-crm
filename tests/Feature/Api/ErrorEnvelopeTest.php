<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ErrorEnvelopeTest extends TestCase
{
    use RefreshDatabase;

    public function test_validation_error_returns_error_envelope(): void
    {
        $response = $this->postJson('/api/v1/branches', []);

        $response->assertStatus(422);
        $response->assertJsonStructure([
            'error' => [
                'code',
                'message',
                'field_errors',
                'request_id',
            ],
        ]);
        $this->assertEquals('validation_failed', $response->json('error.code'));
    }

    public function test_per_page_validation_error(): void
    {
        $response = $this->getJson('/api/v1/branches?per_page=999');

        $response->assertStatus(422);
        $this->assertEquals('validation_failed', $response->json('error.code'));
        $this->assertNotNull($response->json('error.field_errors.per_page'));
    }

    public function test_unknown_sort_returns_validation_error(): void
    {
        $response = $this->getJson('/api/v1/branches?sort=unknown_field');

        $response->assertStatus(422);
        $this->assertEquals('validation_failed', $response->json('error.code'));
    }

    public function test_unknown_filter_returns_validation_error(): void
    {
        $response = $this->getJson('/api/v1/branches?filter[unknown][eq]=value');

        $response->assertStatus(422);
        $this->assertEquals('validation_failed', $response->json('error.code'));
    }

    public function test_unknown_include_returns_validation_error(): void
    {
        $response = $this->getJson('/api/v1/branches?include=unknown_relation');

        $response->assertStatus(422);
        $this->assertEquals('validation_failed', $response->json('error.code'));
    }

    public function test_not_found_returns_error_envelope(): void
    {
        $response = $this->getJson('/api/v1/branches/nonexistent-uuid');

        $response->assertStatus(404);
        $response->assertJsonStructure([
            'error' => [
                'code',
                'message',
                'request_id',
            ],
        ]);
        $this->assertEquals('not_found', $response->json('error.code'));
    }

    public function test_error_envelope_includes_request_id(): void
    {
        $response = $this->getJson('/api/v1/branches?per_page=999');

        $this->assertNotNull($response->headers->get('X-Request-Id'));
        $this->assertEquals($response->headers->get('X-Request-Id'), $response->json('error.request_id'));
    }

    public function test_error_code_is_enum_value(): void
    {
        $response = $this->getJson('/api/v1/branches?per_page=999');

        $code = $response->json('error.code');
        $this->assertIsString($code);
        $this->assertNotEmpty($code);
        $this->assertStringNotContainsString(' ', $code);
    }
}
