<?php

namespace Tests\Feature\Ticketing;

use App\Domains\Ticketing\Models\Ticket;
use App\Models\User;
use Tests\TestCase;

class TicketMergeTest extends TestCase
{
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_merge_moves_tags_to_target(): void
    {
        $source = Ticket::factory()->create();
        $target = Ticket::factory()->create();
        $tag = $source->tags()->attach(1);

        $response = $this->actingAs($this->user)->postJson("/api/v1/tickets/{$source->uuid}/merge", [
            'target' => $target->uuid,
        ]);

        $response->assertOk();
        $this->assertTrue($target->fresh()->tags()->where('id', 1)->exists());
    }

    public function test_merge_marks_source_as_merged(): void
    {
        $source = Ticket::factory()->create();
        $target = Ticket::factory()->create();

        $response = $this->actingAs($this->user)->postJson("/api/v1/tickets/{$source->uuid}/merge", [
            'target' => $target->uuid,
        ]);

        $response->assertOk();
        $this->assertNotNull($source->fresh()->merged_into_ticket_id);
        $this->assertEquals($target->id, $source->fresh()->merged_into_ticket_id);
    }

    public function test_merge_self_rejected(): void
    {
        $ticket = Ticket::factory()->create();

        $response = $this->actingAs($this->user)->postJson("/api/v1/tickets/{$ticket->uuid}/merge", [
            'target' => $ticket->uuid,
        ]);

        $response->assertConflict();
        $response->assertJsonPath('error.code', 'ticket.cannot_merge_into_itself');
    }

    public function test_merge_already_merged_source_rejected(): void
    {
        $source = Ticket::factory()->create();
        $intermediate = Ticket::factory()->create();
        $target = Ticket::factory()->create();

        $source->update(['merged_into_ticket_id' => $intermediate->id]);

        $response = $this->actingAs($this->user)->postJson("/api/v1/tickets/{$source->uuid}/merge", [
            'target' => $target->uuid,
        ]);

        $response->assertConflict();
        $response->assertJsonPath('error.code', 'ticket.already_merged');
    }
}
