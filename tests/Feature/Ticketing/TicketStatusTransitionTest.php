<?php

namespace Tests\Feature\Ticketing;

use App\Domains\Ticketing\Models\Ticket;
use App\Domains\Ticketing\Models\TicketStatusDefinition;
use App\Models\User;
use Tests\TestCase;

class TicketStatusTransitionTest extends TestCase
{
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_legal_transition_succeeds(): void
    {
        $ticket = Ticket::factory()->create();
        $openStatus = TicketStatusDefinition::where('lifecycle_type', 'open')->first();

        $response = $this->actingAs($this->user)->postJson("/api/v1/tickets/{$ticket->uuid}/status", [
            'status' => $openStatus->uuid,
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('ticket_events', [
            'ticket_id' => $ticket->id,
            'type' => 'status_changed',
        ]);
    }

    public function test_illegal_transition_rejected(): void
    {
        $ticket = Ticket::factory()->create(['status' => 'closed']);
        $closedStatus = TicketStatusDefinition::where('lifecycle_type', 'closed')->first();
        $ticket->update(['ticket_status_id' => $closedStatus->id]);

        $openStatus = TicketStatusDefinition::where('lifecycle_type', 'open')->first();

        $response = $this->actingAs($this->user)->postJson("/api/v1/tickets/{$ticket->uuid}/status", [
            'status' => $openStatus->uuid,
        ]);

        $response->assertUnprocessable();
        $response->assertJsonPath('error.code', 'ticket.illegal_transition');
    }

    public function test_same_status_is_no_op(): void
    {
        $ticket = Ticket::factory()->create();
        $currentStatus = $ticket->status;
        $eventCountBefore = $ticket->events()->count();

        $response = $this->actingAs($this->user)->postJson("/api/v1/tickets/{$ticket->uuid}/status", [
            'status' => $currentStatus->uuid,
        ]);

        $response->assertOk();
        $this->assertEquals($eventCountBefore, $ticket->fresh()->events()->count());
    }

    public function test_transition_requiring_reason_without_reason_rejected(): void
    {
        $ticket = Ticket::factory()->create();
        $spamStatus = TicketStatusDefinition::where('lifecycle_type', 'spam')->first();

        $response = $this->actingAs($this->user)->postJson("/api/v1/tickets/{$ticket->uuid}/status", [
            'status' => $spamStatus->uuid,
        ]);

        $response->assertUnprocessable();
        $response->assertJsonPath('error.code', 'ticket.transition_reason_required');
    }

    public function test_transition_requiring_reason_with_reason_succeeds(): void
    {
        $ticket = Ticket::factory()->create();
        $spamStatus = TicketStatusDefinition::where('lifecycle_type', 'spam')->first();

        $response = $this->actingAs($this->user)->postJson("/api/v1/tickets/{$ticket->uuid}/status", [
            'status' => $spamStatus->uuid,
            'reason' => 'This is spam',
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'spam_marked_at' => now(),
        ]);
    }

    public function test_transition_writes_event_with_payload(): void
    {
        $ticket = Ticket::factory()->create();
        $openStatus = TicketStatusDefinition::where('lifecycle_type', 'open')->first();

        $response = $this->actingAs($this->user)->postJson("/api/v1/tickets/{$ticket->uuid}/status", [
            'status' => $openStatus->uuid,
            'reason' => 'Moving forward',
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('ticket_events', [
            'ticket_id' => $ticket->id,
            'type' => 'status_changed',
        ]);
    }
}
