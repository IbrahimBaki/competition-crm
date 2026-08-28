<?php

namespace Tests\Feature\Ticketing;

use App\Domains\Ticketing\Models\MessageChannel;
use App\Domains\Ticketing\Models\Ticket;
use App\Domains\Ticketing\Models\TicketMessage;
use App\Models\User;
use Tests\Support\InteractsWithPermissions;
use Tests\TestCase;

class TicketInternalNoteVisibilityTest extends TestCase
{
    use InteractsWithPermissions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutExceptionHandling();
    }

    public function test_agent_with_internal_view_permission_sees_internal_notes(): void
    {
        $ticket = Ticket::factory()->create();
        $agent = User::factory()->create();
        $this->grantPermission($agent, 'ticket.message.view');
        $this->grantPermission($agent, 'ticket.message.internal.view');

        $internalNote = TicketMessage::factory()
            ->internal()
            ->create(['ticket_id' => $ticket->id]);

        $response = $this->actingAs($agent)->getJson(
            "/api/v1/tickets/{$ticket->uuid}/messages"
        );

        $uuids = collect($response->json('data'))->pluck('uuid');
        $this->assertContains($internalNote->uuid, $uuids);
    }

    public function test_agent_without_internal_view_permission_does_not_see_internal_notes(): void
    {
        $ticket = Ticket::factory()->create();
        $agent = User::factory()->create();
        $this->grantPermission($agent, 'ticket.message.view');

        $internalNote = TicketMessage::factory()
            ->internal()
            ->create(['ticket_id' => $ticket->id]);

        $publicMessage = TicketMessage::factory()
            ->create(['ticket_id' => $ticket->id, 'is_internal' => false]);

        $response = $this->actingAs($agent)->getJson(
            "/api/v1/tickets/{$ticket->uuid}/messages"
        );

        $uuids = collect($response->json('data'))->pluck('uuid');
        $this->assertNotContains($internalNote->uuid, $uuids);
        $this->assertContains($publicMessage->uuid, $uuids);
    }

    public function test_internal_note_has_null_delivery_state(): void
    {
        $ticket = Ticket::factory()->create();
        $agent = User::factory()->create();
        $this->grantPermission($agent, 'ticket.message.view');
        $this->grantPermission($agent, 'ticket.message.internal.view');
        $this->grantPermission($agent, 'ticket.message.internal.write');

        $response = $this->actingAs($agent)->postJson(
            "/api/v1/tickets/{$ticket->uuid}/messages",
            [
                'body' => 'Internal note',
                'channel' => MessageChannel::Email->value,
                'is_internal' => true,
            ]
        );

        $response->assertCreated();
        $this->assertNull($response->json('data.delivery_state'));
    }

    public function test_delivery_events_forbidden_for_internal_note_without_permission(): void
    {
        $ticket = Ticket::factory()->create();
        $agent = User::factory()->create();
        $this->grantPermission($agent, 'ticket.message.view');

        $internalNote = TicketMessage::factory()
            ->internal()
            ->create(['ticket_id' => $ticket->id]);

        $response = $this->actingAs($agent)->getJson(
            "/api/v1/tickets/{$ticket->uuid}/messages/{$internalNote->uuid}/delivery-events"
        );

        // Should get 404 because internal note isn't visible to this user
        $response->assertNotFound();
    }
}
