<?php

namespace Tests\Feature\Ticketing;

use App\Domains\Ticketing\Models\MessageChannel;
use App\Domains\Ticketing\Models\MessageDeliveryState;
use App\Domains\Ticketing\Models\MessageDirection;
use App\Domains\Ticketing\Models\Ticket;
use App\Domains\Ticketing\Models\TicketMessage;
use App\Models\User;
use Illuminate\Support\Str;
use Tests\Support\InteractsWithPermissions;
use Tests\TestCase;

class Story462IntegrationTest extends TestCase
{
    use InteractsWithPermissions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutExceptionHandling();
    }

    public function test_story_462_complete_message_workflow(): void
    {
        // Setup
        $ticket = Ticket::factory()->create();
        $agent = User::factory()->create();
        $this->grantPermission($agent, 'ticket.message.view');
        $this->grantPermission($agent, 'ticket.message.send');
        $this->grantPermission($agent, 'ticket.message.internal.view');
        $this->grantPermission($agent, 'ticket.message.internal.write');
        $this->grantPermission($agent, 'ticket.message.retry');

        // 1. Post a message with all required fields
        $postResponse = $this->actingAs($agent)->postJson(
            "/api/v1/tickets/{$ticket->uuid}/messages",
            [
                'body' => 'Customer inquiry',
                'channel' => MessageChannel::Email->value,
            ]
        );

        $postResponse->assertCreated();
        $messageUuid = $postResponse->json('data.uuid');

        // Verify message fields
        $this->assertNotNull($messageUuid);
        $this->assertEquals('Customer inquiry', $postResponse->json('data.body'));
        $this->assertEquals(MessageDirection::Outbound->value, $postResponse->json('data.direction'));
        $this->assertEquals(MessageDeliveryState::Queued->value, $postResponse->json('data.delivery_state'));
        $this->assertFalse($postResponse->json('data.is_internal'));

        // 2. Internal note should be excluded from non-privileged users
        $restrictedAgent = User::factory()->create();
        $this->grantPermission($restrictedAgent, 'ticket.message.view');
        $this->grantPermission($restrictedAgent, 'ticket.message.send');

        $internalResponse = $this->actingAs($restrictedAgent)->postJson(
            "/api/v1/tickets/{$ticket->uuid}/messages",
            [
                'body' => 'Internal note for agent eyes only',
                'channel' => MessageChannel::Email->value,
                'is_internal' => true,
            ]
        );

        $internalResponse->assertStatus(403);  // Should fail without internal_write permission

        // 3. Post internal note with proper permissions
        $internalResponse = $this->actingAs($agent)->postJson(
            "/api/v1/tickets/{$ticket->uuid}/messages",
            [
                'body' => 'Internal note for agent eyes only',
                'channel' => MessageChannel::Email->value,
                'is_internal' => true,
            ]
        );

        $internalResponse->assertCreated();
        $this->assertNull($internalResponse->json('data.delivery_state'));
        $this->assertTrue($internalResponse->json('data.is_internal'));

        // 4. List messages - restricted user should only see public message
        $listResponse = $this->actingAs($restrictedAgent)->getJson(
            "/api/v1/tickets/{$ticket->uuid}/messages"
        );

        $messages = $listResponse->json('data');
        $this->assertCount(1, $messages);
        $this->assertFalse($messages[0]['is_internal']);

        // 5. Agent with internal permission should see both
        $listResponse = $this->actingAs($agent)->getJson(
            "/api/v1/tickets/{$ticket->uuid}/messages"
        );

        $messages = $listResponse->json('data');
        $this->assertCount(2, $messages);

        // 6. Idempotency - same key produces same response
        $idempotencyKey = Str::uuid()->toString();
        $response1 = $this->actingAs($agent)
            ->withHeader('Idempotency-Key', $idempotencyKey)
            ->postJson(
                "/api/v1/tickets/{$ticket->uuid}/messages",
                [
                    'body' => 'Idempotent message',
                    'channel' => MessageChannel::Email->value,
                ]
            );

        $response2 = $this->actingAs($agent)
            ->withHeader('Idempotency-Key', $idempotencyKey)
            ->postJson(
                "/api/v1/tickets/{$ticket->uuid}/messages",
                [
                    'body' => 'Idempotent message',
                    'channel' => MessageChannel::Email->value,
                ]
            );

        $this->assertEquals($response1->json(), $response2->json());
        $this->assertEquals(3, Ticket::find($ticket->id)->messages()->count());

        // 7. Delivery state transitions
        $message = TicketMessage::where('uuid', $messageUuid)->first();
        $this->assertEquals(MessageDeliveryState::Queued, $message->delivery_state);
        $this->assertCount(1, $message->deliveryEvents);

        // 8. Failed message retry
        $message->update([
            'delivery_state' => MessageDeliveryState::Failed,
            'failure_reason' => 'provider_rejected',
            'failed_at' => now(),
        ]);
        $message->deliveryEvents()->create([
            'from_state' => MessageDeliveryState::Queued,
            'to_state' => MessageDeliveryState::Failed,
            'reason' => 'provider_rejected',
            'occurred_at' => now(),
        ]);

        // 9. Retry should re-queue
        $retryResponse = $this->actingAs($agent)->postJson(
            "/api/v1/tickets/{$ticket->uuid}/messages/{$messageUuid}/retry"
        );

        $retryResponse->assertOk();
        $message->refresh();
        $this->assertEquals(MessageDeliveryState::Queued, $message->delivery_state);
        $this->assertEquals(1, $message->retry_count);
        $this->assertNull($message->failed_at);
        $this->assertCount(3, $message->deliveryEvents);

        // 10. Verify delivery events history
        $eventsResponse = $this->actingAs($agent)->getJson(
            "/api/v1/tickets/{$ticket->uuid}/messages/{$messageUuid}/delivery-events"
        );

        $events = $eventsResponse->json('data');
        $this->assertCount(3, $events);  // queued -> failed -> queued (retry)
        $this->assertNull($events[0]['from_state']);
        $this->assertEquals('queued', $events[0]['to_state']);
        $this->assertEquals('failed', $events[1]['to_state']);
        $this->assertEquals('queued', $events[2]['to_state']);
    }
}
