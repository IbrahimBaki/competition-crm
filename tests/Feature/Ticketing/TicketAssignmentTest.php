<?php

namespace Tests\Feature\Ticketing;

use App\Domains\Ticketing\Models\Ticket;
use App\Domains\Ticketing\Models\TicketEventType;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class TicketAssignmentTest extends TestCase
{
    use DatabaseMigrations;

    private Ticket $ticket;

    private User $agent;

    private User $manager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutExceptionHandling();

        $this->ticket = Ticket::factory()->create();
        $this->agent = User::factory()->create();
        $this->manager = User::factory()->create();

        // Grant permissions
        $this->agent->grantPermission('tickets.assign');
        $this->agent->grantPermission('tickets.claim');
        $this->agent->grantPermission('tickets.queue.view');
        $this->manager->grantPermission('tickets.assign');
        $this->manager->grantPermission('tickets.transfer.agent');
        $this->manager->grantPermission('tickets.transfer.department');
    }

    public function test_assign_endpoint_returns_ticket_with_assignee(): void
    {
        $response = $this->actingAs($this->manager)->postJson(
            "/api/v1/tickets/{$this->ticket->uuid}/assign",
            ['assignee_uuid' => $this->agent->uuid]
        );

        $response->assertOk();
        $this->assertNotNull($response->json('data.assignee_id'));
        $this->assertEquals($this->agent->uuid, $response->json('data.assignee_id'));
    }

    public function test_assign_requires_permission(): void
    {
        $userWithoutPermission = User::factory()->create();

        $response = $this->actingAs($userWithoutPermission)->postJson(
            "/api/v1/tickets/{$this->ticket->uuid}/assign",
            ['assignee_uuid' => $this->agent->uuid]
        );

        $response->assertForbidden();
    }

    public function test_assign_records_audit_event(): void
    {
        $this->actingAs($this->manager)->postJson(
            "/api/v1/tickets/{$this->ticket->uuid}/assign",
            ['assignee_uuid' => $this->agent->uuid]
        );

        $event = $this->ticket->fresh()->events()
            ->where('event_type', TicketEventType::Assigned->value)
            ->first();

        $this->assertNotNull($event);
        $this->assertEquals($this->manager->id, $event->actor_user_id);
        $this->assertEquals($this->agent->uuid, $event->payload['user_uuid']);
        $this->assertNull($event->payload['previous_user_uuid']);
    }

    public function test_unassign_clears_assignee(): void
    {
        $this->ticket->update(['assigned_user_id' => $this->agent->id]);

        $response = $this->actingAs($this->manager)->deleteJson(
            "/api/v1/tickets/{$this->ticket->uuid}/assign"
        );

        $response->assertOk();
        $this->assertNull($response->json('data.assignee_id'));
    }

    public function test_claim_unowned_ticket(): void
    {
        $response = $this->actingAs($this->agent)->postJson(
            "/api/v1/tickets/{$this->ticket->uuid}/claim"
        );

        $response->assertOk();
        $this->assertEquals($this->agent->uuid, $response->json('data.assignee_id'));
    }

    public function test_claim_already_claimed_ticket_returns_conflict(): void
    {
        $otherAgent = User::factory()->create();
        $this->ticket->update(['assigned_user_id' => $otherAgent->id]);

        $response = $this->actingAs($this->agent)->postJson(
            "/api/v1/tickets/{$this->ticket->uuid}/claim"
        );

        $response->assertConflict();
        $response->assertJsonPath('error.code', 'ticket.already_claimed');
    }

    public function test_reclaim_own_ticket_is_idempotent(): void
    {
        $this->ticket->update(['assigned_user_id' => $this->agent->id]);

        $response = $this->actingAs($this->agent)->postJson(
            "/api/v1/tickets/{$this->ticket->uuid}/claim"
        );

        $response->assertOk();
        // No new event should be recorded
        $claimEvents = $this->ticket->fresh()->events()
            ->where('event_type', TicketEventType::Claimed->value)
            ->count();
        $this->assertEquals(0, $claimEvents);
    }

    public function test_transfer_to_agent(): void
    {
        $newAgent = User::factory()->create();

        $response = $this->actingAs($this->manager)->postJson(
            "/api/v1/tickets/{$this->ticket->uuid}/transfer/agent",
            ['user_uuid' => $newAgent->uuid]
        );

        $response->assertOk();
        $this->assertEquals($newAgent->uuid, $response->json('data.assignee_id'));
    }

    public function test_transfer_to_agent_requires_permission(): void
    {
        $newAgent = User::factory()->create();

        $response = $this->actingAs($this->agent)->postJson(
            "/api/v1/tickets/{$this->ticket->uuid}/transfer/agent",
            ['user_uuid' => $newAgent->uuid]
        );

        $response->assertForbidden();
    }

    public function test_transfer_to_department_clears_assignee_by_default(): void
    {
        $newDepartment = $this->ticket->department()->first();
        $this->ticket->update(['assigned_user_id' => $this->agent->id]);

        $response = $this->actingAs($this->manager)->postJson(
            "/api/v1/tickets/{$this->ticket->uuid}/transfer/department",
            ['department_id' => $newDepartment->id]
        );

        $response->assertOk();
        $this->assertNull($response->json('data.assignee_id'));
    }

    public function test_transfer_to_department_keeps_assignee_when_requested(): void
    {
        $newDepartment = $this->ticket->department()->first();
        $this->ticket->update(['assigned_user_id' => $this->agent->id]);

        $response = $this->actingAs($this->manager)->postJson(
            "/api/v1/tickets/{$this->ticket->uuid}/transfer/department",
            ['department_id' => $newDepartment->id, 'keep_assignee' => true]
        );

        $response->assertOk();
        $this->assertEquals($this->agent->uuid, $response->json('data.assignee_id'));
    }
}
