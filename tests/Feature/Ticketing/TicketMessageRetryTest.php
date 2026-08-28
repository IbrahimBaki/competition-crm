<?php

namespace Tests\Feature\Ticketing;

use App\Domains\Ticketing\Models\MessageDeliveryState;
use App\Domains\Ticketing\Models\Ticket;
use App\Domains\Ticketing\Models\TicketMessage;
use App\Models\User;
use Tests\Support\InteractsWithPermissions;
use Tests\TestCase;

class TicketMessageRetryTest extends TestCase
{
    use InteractsWithPermissions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutExceptionHandling();
    }

    public function test_failed_messages_are_filterable(): void
    {
        $ticket = Ticket::factory()->create();
        $agent = User::factory()->create();
        $this->grantPermission($agent, 'ticket.message.view');

        $failedMessage = TicketMessage::factory()
            ->failed()
            ->create(['ticket_id' => $ticket->id]);

        $sentMessage = TicketMessage::factory()
            ->queued()
            ->create(['ticket_id' => $ticket->id, 'delivery_state' => MessageDeliveryState::Sent]);

        $response = $this->actingAs($agent)->getJson(
            "/api/v1/tickets/{$ticket->uuid}/messages?filter[delivery_state]=".MessageDeliveryState::Failed->value
        );

        $uuids = collect($response->json('data'))->pluck('uuid');
        $this->assertContains($failedMessage->uuid, $uuids);
        $this->assertNotContains($sentMessage->uuid, $uuids);
    }

    public function test_retry_re_queues_same_row(): void
    {
        $ticket = Ticket::factory()->create();
        $agent = User::factory()->create();
        $this->grantPermission($agent, 'ticket.message.view');
        $this->grantPermission($agent, 'ticket.message.retry');

        $failedMessage = TicketMessage::factory()
            ->failed()
            ->create(['ticket_id' => $ticket->id, 'retry_count' => 0]);

        $originalId = $failedMessage->id;

        $response = $this->actingAs($agent)->postJson(
            "/api/v1/tickets/{$ticket->uuid}/messages/{$failedMessage->uuid}/retry"
        );

        $response->assertOk();

        // Verify same row
        $retried = TicketMessage::find($originalId);
        $this->assertEquals($originalId, $retried->id);
        $this->assertEquals(MessageDeliveryState::Queued, $retried->delivery_state);
        $this->assertEquals(1, $retried->retry_count);
    }

    public function test_retry_increments_retry_count(): void
    {
        $ticket = Ticket::factory()->create();
        $agent = User::factory()->create();
        $this->grantPermission($agent, 'ticket.message.view');
        $this->grantPermission($agent, 'ticket.message.retry');

        $failedMessage = TicketMessage::factory()
            ->failed()
            ->create(['ticket_id' => $ticket->id, 'retry_count' => 2]);

        $this->actingAs($agent)->postJson(
            "/api/v1/tickets/{$ticket->uuid}/messages/{$failedMessage->uuid}/retry"
        );

        $retried = TicketMessage::find($failedMessage->id);
        $this->assertEquals(3, $retried->retry_count);
    }

    public function test_retry_non_failed_message_returns_409(): void
    {
        $ticket = Ticket::factory()->create();
        $agent = User::factory()->create();
        $this->grantPermission($agent, 'ticket.message.view');
        $this->grantPermission($agent, 'ticket.message.retry');

        $sentMessage = TicketMessage::factory()
            ->queued()
            ->create(['ticket_id' => $ticket->id, 'delivery_state' => MessageDeliveryState::Sent]);

        $response = $this->actingAs($agent)->postJson(
            "/api/v1/tickets/{$ticket->uuid}/messages/{$sentMessage->uuid}/retry"
        );

        $response->assertStatus(409);
        $response->assertJsonPath('error.code', 'message_not_retryable');
    }

    public function test_retry_creates_new_delivery_event(): void
    {
        $ticket = Ticket::factory()->create();
        $agent = User::factory()->create();
        $this->grantPermission($agent, 'ticket.message.view');
        $this->grantPermission($agent, 'ticket.message.retry');

        $failedMessage = TicketMessage::factory()
            ->failed()
            ->create(['ticket_id' => $ticket->id]);

        $eventCountBefore = $failedMessage->deliveryEvents->count();

        $this->actingAs($agent)->postJson(
            "/api/v1/tickets/{$ticket->uuid}/messages/{$failedMessage->uuid}/retry"
        );

        $failedMessage->refresh();
        $eventCountAfter = $failedMessage->deliveryEvents->count();

        $this->assertEquals($eventCountBefore + 1, $eventCountAfter);
    }
}
