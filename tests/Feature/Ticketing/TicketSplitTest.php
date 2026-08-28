<?php

namespace Tests\Feature\Ticketing;

use App\Domains\Ticketing\Models\Ticket;
use App\Models\User;
use Tests\TestCase;

class TicketSplitTest extends TestCase
{
    public function test_split_creates_child_ticket(): void
    {
        $user = User::factory()->create();
        $parent = Ticket::factory()->create();

        $response = $this->actingAs($user)->postJson("/api/v1/tickets/{$parent->uuid}/split", [
            'subject' => 'Child ticket',
            'body' => 'This is a child',
        ]);

        $response->assertCreated();
        $this->assertEquals($parent->id, $response->json('data.parent_ticket_id'));
    }
}
