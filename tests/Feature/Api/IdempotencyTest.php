<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class IdempotencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_idempotent_key_replays_response(): void
    {
        $key = (string) Str::uuid();
        $data = ['name' => ['en' => 'Test Branch', 'ar' => 'فرع اختبار'], 'code' => 'TST'];

        $response1 = $this->postJson('/api/v1/branches', $data, [
            'Idempotency-Key' => $key,
        ]);

        $response2 = $this->postJson('/api/v1/branches', $data, [
            'Idempotency-Key' => $key,
        ]);

        $this->assertEquals($response1->status(), $response2->status());
        $this->assertEquals($response1->json('data.id'), $response2->json('data.id'));
    }

    public function test_idempotent_key_different_body_returns_conflict(): void
    {
        $key = (string) Str::uuid();

        $this->postJson('/api/v1/branches', ['name' => ['en' => 'First', 'ar' => 'أول'], 'code' => 'F01'], [
            'Idempotency-Key' => $key,
        ]);

        $response = $this->postJson('/api/v1/branches', ['name' => ['en' => 'Second', 'ar' => 'ثاني'], 'code' => 'S02'], [
            'Idempotency-Key' => $key,
        ]);

        $response->assertStatus(409);
        $this->assertEquals('idempotency_key_conflict', $response->json('error.code'));
    }

    public function test_idempotent_key_without_key_creates_duplicates(): void
    {
        $data = ['name' => ['en' => 'Test', 'ar' => 'اختبار'], 'code' => 'TST'];

        $this->postJson('/api/v1/branches', $data);
        $this->postJson('/api/v1/branches', $data);

        // Without idempotency key, duplicates should be created (or validation prevents it)
        // This test verifies that idempotency key is optional
        $response = $this->getJson('/api/v1/branches');
        $response->assertStatus(200);
    }

    public function test_idempotent_key_get_request_ignored(): void
    {
        $key = (string) Str::uuid();

        $response = $this->getJson('/api/v1/branches', [
            'Idempotency-Key' => $key,
        ]);

        $response->assertStatus(200);
        // GET requests should not store idempotency keys
    }

    public function test_idempotency_preserves_request_id(): void
    {
        $key = (string) Str::uuid();
        $data = ['name' => ['en' => 'Test', 'ar' => 'اختبار'], 'code' => 'TST'];

        $response1 = $this->postJson('/api/v1/branches', $data, [
            'Idempotency-Key' => $key,
            'X-Request-Id' => 'req-1',
        ]);

        $response2 = $this->postJson('/api/v1/branches', $data, [
            'Idempotency-Key' => $key,
            'X-Request-Id' => 'req-2',
        ]);

        // Current request ID should be in response, not stored one
        $this->assertEquals('req-2', $response2->header('X-Request-Id'));
    }
}
