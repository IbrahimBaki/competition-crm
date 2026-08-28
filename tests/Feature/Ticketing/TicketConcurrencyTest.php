<?php

namespace Tests\Feature\Ticketing;

use App\Domains\Ticketing\Models\Ticket;
use App\Models\User;
use Tests\Support\InteractsWithPermissions;
use Tests\TestCase;

class TicketConcurrencyTest extends TestCase
{
    use InteractsWithPermissions;

    private Ticket $ticket;

    private User $manager;

    private User $agent1;

    private User $agent2;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutExceptionHandling();

        $this->ticket = Ticket::factory()->create();
        $this->manager = User::factory()->create();
        $this->agent1 = User::factory()->create();
        $this->agent2 = User::factory()->create();

        $this->grantPermission($this->manager, 'tickets.assign');
        $this->grantPermission($this->manager, 'tickets.transfer.agent');
        $this->grantPermission($this->manager, 'tickets.claim');
        $this->grantPermission($this->agent1, 'tickets.claim');
        $this->grantPermission($this->agent2, 'tickets.claim');
    }

    public function test_stale_version_on_assign_returns_conflict(): void
    {
        $response = $this->actingAs($this->manager)->postJson(
            "/api/v1/tickets/{$this->ticket->uuid}/assign",
            ['assignee_uuid' => $this->agent1->uuid, 'version' => 999]
        );

        $response->assertConflict();
        $response->assertJsonPath('error.code', 'ticket.version_conflict');
        $this->assertNotNull($response->json('error.details.current_version'));
    }

    public function test_conflict_response_includes_current_state(): void
    {
        $this->ticket->update(['assigned_user_id' => $this->agent1->id]);

        $response = $this->actingAs($this->manager)->postJson(
            "/api/v1/tickets/{$this->ticket->uuid}/assign",
            ['assignee_uuid' => $this->agent2->uuid, 'version' => 1]
        );

        $response->assertConflict();
        $this->assertNotNull($response->json('error.details.current_assignee_uuid'));
        $this->assertEquals($this->agent1->uuid, $response->json('error.details.current_assignee_uuid'));
    }

    public function test_concurrent_claims_one_succeeds_one_fails(): void
    {
        // Simulate two concurrent claims by two agents
        $response1 = $this->actingAs($this->agent1)->postJson(
            "/api/v1/tickets/{$this->ticket->uuid}/claim"
        );

        // Immediately try a second claim (this simulates concurrency by re-reading
        // the state after the first claim changed it)
        $response2 = $this->actingAs($this->agent2)->postJson(
            "/api/v1/tickets/{$this->ticket->uuid}/claim"
        );

        // One should succeed
        $this->assertTrue(
            $response1->status() === 200 || $response2->status() === 200,
            'At least one claim should succeed'
        );

        // One should fail with conflict
        $this->assertTrue(
            $response1->status() === 409 || $response2->status() === 409,
            'Exactly one claim should fail with conflict'
        );

        // The failed one should be already_claimed
        if ($response2->status() === 409) {
            $response2->assertJsonPath('error.code', 'ticket.already_claimed');
        }
    }

    public function test_version_increments_after_assignment(): void
    {
        $initialVersion = $this->ticket->version;

        $this->actingAs($this->manager)->postJson(
            "/api/v1/tickets/{$this->ticket->uuid}/assign",
            ['assignee_uuid' => $this->agent1->uuid]
        );

        $this->assertEquals($initialVersion + 1, $this->ticket->fresh()->version);
    }
}
