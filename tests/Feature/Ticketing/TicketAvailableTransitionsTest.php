<?php

namespace Tests\Feature\Ticketing;

use App\Domains\Ticketing\Models\Ticket;
use App\Models\User;
use Tests\TestCase;

class TicketAvailableTransitionsTest extends TestCase
{
    public function test_ticket_response_includes_available_transitions(): void
    {
        $user = User::factory()->create();
        $ticket = Ticket::factory()->create();

        $response = $this->actingAs($user)->getJson("/api/v1/tickets/{$ticket->uuid}");

        $response->assertOk();
        $this->assertIsArray($response->json('data.available_transitions'));
    }

    public function test_available_transitions_empty_for_merged_ticket(): void
    {
        $user = User::factory()->create();
        $ticket = Ticket::factory()->create(['merged_into_ticket_id' => 1]);

        $response = $this->actingAs($user)->getJson("/api/v1/tickets/{$ticket->uuid}");

        $response->assertOk();
        $this->assertEmpty($response->json('data.available_transitions'));
    }
}
