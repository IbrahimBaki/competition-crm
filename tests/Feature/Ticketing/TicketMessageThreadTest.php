<?php

namespace Tests\Feature\Ticketing;

use App\Domains\Ticketing\Models\MessageChannel;
use App\Domains\Ticketing\Models\MessageDeliveryState;
use App\Domains\Ticketing\Models\MessageDirection;
use App\Domains\Ticketing\Models\Ticket;
use App\Models\User;
use Tests\Support\InteractsWithPermissions;
use Tests\TestCase;

class TicketMessageThreadTest extends TestCase
{
    use InteractsWithPermissions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutExceptionHandling();
    }

    public function test_agent_can_post_message_to_ticket(): void
    {
        $ticket = Ticket::factory()->create();
        $agent = User::factory()->create();
        $this->grantPermission($agent, 'ticket.message.view');
        $this->grantPermission($agent, 'ticket.message.send');

        $response = $this->actingAs($agent)->postJson(
            "/api/v1/tickets/{$ticket->uuid}/messages",
            [
                'body' => 'This is a test message',
                'channel' => MessageChannel::Email->value,
            ]
        );

        $response->assertCreated();
        $this->assertDatabaseHas('ticket_messages', [
            'ticket_id' => $ticket->id,
            'body' => 'This is a test message',
            'direction' => MessageDirection::Outbound->value,
            'delivery_state' => MessageDeliveryState::Queued->value,
        ]);
    }

    public function test_message_response_contains_expected_fields(): void
    {
        $ticket = Ticket::factory()->create();
        $agent = User::factory()->create();
        $this->grantPermission($agent, 'ticket.message.view');
        $this->grantPermission($agent, 'ticket.message.send');

        $response = $this->actingAs($agent)->postJson(
            "/api/v1/tickets/{$ticket->uuid}/messages",
            [
                'body' => 'Test message',
                'channel' => MessageChannel::Email->value,
            ]
        );

        $response->assertJsonStructure([
            'data' => [
                'uuid',
                'direction',
                'author_type',
                'author',
                'channel',
                'is_internal',
                'body',
                'body_format',
                'delivery_state',
                'retry_count',
                'created_at',
                'attachments',
            ],
        ]);
    }

    public function test_messages_appear_in_chronological_order(): void
    {
        $ticket = Ticket::factory()->create();
        $agent = User::factory()->create();
        $this->grantPermission($agent, 'ticket.message.view');
        $this->grantPermission($agent, 'ticket.message.send');

        $this->actingAs($agent)->postJson(
            "/api/v1/tickets/{$ticket->uuid}/messages",
            ['body' => 'First', 'channel' => MessageChannel::Email->value]
        );

        $this->actingAs($agent)->postJson(
            "/api/v1/tickets/{$ticket->uuid}/messages",
            ['body' => 'Second', 'channel' => MessageChannel::Email->value]
        );

        $response = $this->actingAs($agent)->getJson(
            "/api/v1/tickets/{$ticket->uuid}/messages"
        );

        $messages = $response->json('data');
        $this->assertCount(2, $messages);
        $this->assertEquals('First', $messages[0]['body']);
        $this->assertEquals('Second', $messages[1]['body']);
    }

    public function test_arabic_body_round_trips_unchanged(): void
    {
        $ticket = Ticket::factory()->create();
        $agent = User::factory()->create();
        $this->grantPermission($agent, 'ticket.message.view');
        $this->grantPermission($agent, 'ticket.message.send');

        $arabicBody = 'مرحبا بك في نظام دعم العملاء';

        $response = $this->actingAs($agent)->postJson(
            "/api/v1/tickets/{$ticket->uuid}/messages",
            [
                'body' => $arabicBody,
                'channel' => MessageChannel::Email->value,
            ]
        );

        $response->assertCreated();
        $this->assertEquals($arabicBody, $response->json('data.body'));
    }
}
