<?php

namespace Tests\Feature\Observability;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HealthEndpointTest extends TestCase
{
    use RefreshDatabase;

    public function test_health_live_returns_ok_unconditionally(): void
    {
        $response = $this->getJson('/api/v1/health/live');

        $this->assertEquals(200, $response->status());
        $this->assertEquals('ok', $response->json('status'));
    }

    public function test_health_ready_returns_ok_when_all_healthy(): void
    {
        $response = $this->getJson('/api/v1/health/ready');

        $this->assertEquals(200, $response->status());
        $this->assertEquals('ok', $response->json('data.status'));
        $this->assertEquals('ok', $response->json('data.checks.app.status'));
        $this->assertEquals('ok', $response->json('data.checks.db.status'));
        $this->assertEquals('ok', $response->json('data.checks.queue.status'));
        $this->assertEquals('ok', $response->json('data.checks.mail.status'));
    }

    public function test_health_ready_includes_per_check_status(): void
    {
        $response = $this->getJson('/api/v1/health/ready');

        $this->assertArrayHasKey('app', $response->json('data.checks'));
        $this->assertArrayHasKey('db', $response->json('data.checks'));
        $this->assertArrayHasKey('queue', $response->json('data.checks'));
        $this->assertArrayHasKey('mail', $response->json('data.checks'));

        foreach (['app', 'db', 'queue', 'mail'] as $check) {
            $this->assertArrayHasKey('status', $response->json("data.checks.$check"));
        }
    }

    public function test_health_ready_does_not_leak_credentials(): void
    {
        $response = $this->getJson('/api/v1/health/ready');

        $body = json_encode($response->json());

        $this->assertStringNotContainsString(config('database.connections.mysql.password'), $body);
        $this->assertStringNotContainsString(config('database.connections.mysql.host'), $body);
        $this->assertStringNotContainsString('mysql://', $body);
    }
}
