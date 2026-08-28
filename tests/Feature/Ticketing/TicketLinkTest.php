<?php

namespace Tests\Feature\Ticketing;

use App\Domains\Ticketing\Models\Ticket;
use App\Domains\Ticketing\Models\TicketLinkRelation;
use App\Models\User;
use Tests\TestCase;

class TicketLinkTest extends TestCase
{
    public function test_link_creates_symmetric_relationship(): void
    {
        $user = User::factory()->create();
        $source = Ticket::factory()->create();
        $target = Ticket::factory()->create();

        $response = $this->actingAs($user)->postJson("/api/v1/tickets/{$source->uuid}/links", [
            'target' => $target->uuid,
            'relation' => TicketLinkRelation::Related->value,
        ]);

        $response->assertCreated();
    }

    public function test_self_link_rejected(): void
    {
        $user = User::factory()->create();
        $ticket = Ticket::factory()->create();

        $response = $this->actingAs($user)->postJson("/api/v1/tickets/{$ticket->uuid}/links", [
            'target' => $ticket->uuid,
            'relation' => TicketLinkRelation::Related->value,
        ]);

        $response->assertUnprocessable();
        $response->assertJsonPath('error.code', 'ticket.cannot_link_to_itself');
    }
}
