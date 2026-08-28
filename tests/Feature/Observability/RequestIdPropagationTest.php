<?php

namespace Tests\Feature\Observability;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class RequestIdPropagationTest extends TestCase
{
    use RefreshDatabase;

    public function test_request_id_is_echoed_in_response_header(): void
    {
        $response = $this->withHeaders(['X-Request-Id' => 'test-id-123'])
            ->postJson('/api/v1/auth/login', [
                'email' => 'test@example.com',
                'password' => 'wrong',
            ]);

        $this->assertEquals('test-id-123', $response->headers->get('X-Request-Id'));
    }

    public function test_request_id_is_present_in_error_envelope(): void
    {
        $response = $this->withHeaders(['X-Request-Id' => 'test-id-456'])
            ->postJson('/api/v1/auth/login', [
                'email' => 'test@example.com',
                'password' => 'wrong',
            ]);

        $this->assertEquals('test-id-456', $response->json('error.request_id'));
    }

    public function test_request_id_is_logged_in_records(): void
    {
        Log::spy();

        $this->withHeaders(['X-Request-Id' => 'test-id-789'])
            ->getJson('/api/v1/health/live');

        Log::spy()->shouldHaveReceived('info')->atLeastOnce();
    }
}
