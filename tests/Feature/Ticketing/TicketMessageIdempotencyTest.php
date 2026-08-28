<?php

namespace Tests\Feature\Ticketing;

use App\Domains\Ticketing\Models\MessageChannel;
use App\Domains\Ticketing\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Str;
use Tests\Support\InteractsWithPermissions;
use Tests\TestCase;

class TicketMessageIdempotencyTest extends TestCase
{
    use InteractsWithPermissions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutExceptionHandling();
    }

    public function test_replayed_idempotency_key_produces_same_response(): void
    {
        $ticket = Ticket::factory()->create();
        $agent = User::factory()->create();
        $this->grantPermission($agent, 'ticket.message.view');
        $this->grantPermission($agent, 'ticket.message.send');

        $idempotencyKey = Str::uuid()->toString();
        $payload = [
            'body' => 'Test message',
            'channel' => MessageChannel::Email->value,
        ];

        $response1 = $this->actingAs($agent)
            ->withHeader('Idempotency-Key', $idempotencyKey)
            ->postJson("/api/v1/tickets/{$ticket->uuid}/messages", $payload);

        $response2 = $this->actingAs($agent)
            ->withHeader('Idempotency-Key', $idempotencyKey)
            ->postJson("/api/v1/tickets/{$ticket->uuid}/messages", $payload);

        // Both should succeed
        $response1->assertCreated();
        $response2->assertCreated();

        // Should return identical responses
        $this->assertEquals($response1->json(), $response2->json());
    }

    public function test_only_one_message_created_with_replayed_key(): void
    {
        $ticket = Ticket::factory()->create();
        $agent = User::factory()->create();
        $this->grantPermission($agent, 'ticket.message.view');
        $this->grantPermission($agent, 'ticket.message.send');

        $idempotencyKey = Str::uuid()->toString();
        $payload = [
            'body' => 'Test message',
            'channel' => MessageChannel::Email->value,
        ];

        $this->actingAs($agent)
            ->withHeader('Idempotency-Key', $idempotencyKey)
            ->postJson("/api/v1/tickets/{$ticket->uuid}/messages", $payload);

        $countBefore = $ticket->messages()->count();

        $this->actingAs($agent)
            ->withHeader('Idempotency-Key', $idempotencyKey)
            ->postJson("/api/v1/tickets/{$ticket->uuid}/messages", $payload);

        $countAfter = $ticket->messages()->count();

        // Should still be only 1 message
        $this->assertEquals(1, $countBefore);
        $this->assertEquals(1, $countAfter);
    }

    public function test_same_key_with_different_body_returns_409(): void
    {
        $ticket = Ticket::factory()->create();
        $agent = User::factory()->create();
        $this->grantPermission($agent, 'ticket.message.view');
        $this->grantPermission($agent, 'ticket.message.send');

        $idempotencyKey = Str::uuid()->toString();

        $this->actingAs($agent)
            ->withHeader('Idempotency-Key', $idempotencyKey)
            ->postJson("/api/v1/tickets/{$ticket->uuid}/messages", [
                'body' => 'First body',
                'channel' => MessageChannel::Email->value,
            ]);

        $response = $this->actingAs($agent)
            ->withHeader('Idempotency-Key', $idempotencyKey)
            ->postJson("/api/v1/tickets/{$ticket->uuid}/messages", [
                'body' => 'Different body',
                'channel' => MessageChannel::Email->value,
            ]);

        $response->assertStatus(409);
        $response->assertJsonPath('error.code', 'idempotency_key_conflict');
    }

    public function test_missing_idempotency_key_allows_duplicates(): void
    {
        $ticket = Ticket::factory()->create();
        $agent = User::factory()->create();
        $this->grantPermission($agent, 'ticket.message.view');
        $this->grantPermission($agent, 'ticket.message.send');

        $payload = [
            'body' => 'Test message',
            'channel' => MessageChannel::Email->value,
        ];

        // Two requests without idempotency key
        $this->actingAs($agent)->postJson("/api/v1/tickets/{$ticket->uuid}/messages", $payload);
        $this->actingAs($agent)->postJson("/api/v1/tickets/{$ticket->uuid}/messages", $payload);

        // Both should be created (no protection without the key)
        $this->assertEquals(2, $ticket->messages()->count());
    }
}
