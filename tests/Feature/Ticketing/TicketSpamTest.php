<?php

namespace Tests\Feature\Ticketing;

use App\Domains\Ticketing\Models\Ticket;
use App\Domains\Ticketing\Models\TicketStatusDefinition;
use App\Models\User;
use Tests\TestCase;

class TicketSpamTest extends TestCase
{
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_mark_spam_preserves_row(): void
    {
        $ticket = Ticket::factory()->create();

        $response = $this->actingAs($this->user)->postJson("/api/v1/tickets/{$ticket->uuid}/spam", [
            'reason' => 'Spam content',
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('tickets', ['id' => $ticket->id]);
    }

    public function test_spam_ticket_can_be_retrieved_by_uuid(): void
    {
        $spamStatus = TicketStatusDefinition::where('lifecycle_type', 'spam')->first();
        $ticket = Ticket::factory()->create([
            'ticket_status_id' => $spamStatus->id,
            'status' => 'spam',
            'spam_marked_at' => now(),
        ]);

        $response = $this->actingAs($this->user)->getJson("/api/v1/tickets/{$ticket->uuid}");

        $response->assertOk();
    }

    public function test_restore_from_spam_requires_permission(): void
    {
        $spamStatus = TicketStatusDefinition::where('lifecycle_type', 'spam')->first();
        $ticket = Ticket::factory()->create([
            'ticket_status_id' => $spamStatus->id,
            'status' => 'spam',
        ]);

        $userWithoutPermission = User::factory()->create();

        $response = $this->actingAs($userWithoutPermission)->deleteJson("/api/v1/tickets/{$ticket->uuid}/spam", [
            'reason' => 'Was a mistake',
        ]);

        $response->assertForbidden();
    }
}
