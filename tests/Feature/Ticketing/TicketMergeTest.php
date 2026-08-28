<?php

namespace Tests\Feature\Ticketing;

use App\Domains\Ticketing\Models\Ticket;
use App\Domains\Ticketing\Models\TicketMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class TicketMergeTest extends TestCase
{
    use DatabaseMigrations;

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

    public function test_merge_repoints_source_messages_to_target(): void
    {
        $source = Ticket::factory()->create();
        $target = Ticket::factory()->create();

        $sourceMessage = TicketMessage::factory()->create(['ticket_id' => $source->id]);
        $targetMessage = TicketMessage::factory()->create(['ticket_id' => $target->id]);

        $response = $this->actingAs($this->user)->postJson("/api/v1/tickets/{$source->uuid}/merge", [
            'target' => $target->uuid,
        ]);

        $response->assertOk();

        $sourceMessage->refresh();
        $this->assertEquals($target->id, $sourceMessage->ticket_id);

        $targetMessage->refresh();
        $this->assertEquals($target->id, $targetMessage->ticket_id);
    }

    public function test_merge_preserves_message_chronology(): void
    {
        $source = Ticket::factory()->create();
        $target = Ticket::factory()->create();

        // Create messages with controlled timestamps
        $oldMessage = TicketMessage::factory()->create([
            'ticket_id' => $source->id,
            'body' => 'From source',
            'created_at' => now()->subHours(2),
        ]);

        $newMessage = TicketMessage::factory()->create([
            'ticket_id' => $target->id,
            'body' => 'From target',
            'created_at' => now()->subHour(),
        ]);

        $this->actingAs($this->user)->postJson("/api/v1/tickets/{$source->uuid}/merge", [
            'target' => $target->uuid,
        ]);

        $messages = $target->messages()->get();
        $this->assertCount(2, $messages);
        $this->assertEquals('From source', $messages[0]->body);
        $this->assertEquals('From target', $messages[1]->body);
    }
}
