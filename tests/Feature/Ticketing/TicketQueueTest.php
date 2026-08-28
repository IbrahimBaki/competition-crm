<?php

namespace Tests\Feature\Ticketing;

use App\Domains\Ticketing\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class TicketQueueTest extends TestCase
{
    use DatabaseMigrations;

    private User $agent;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutExceptionHandling();

        $this->agent = User::factory()->create();
        $this->agent->grantPermission('tickets.queue.view');
        $this->agent->grantPermission('tickets.view.team');
    }

    public function test_personal_queue_returns_assigned_tickets(): void
    {
        $assigned = Ticket::factory()->create(['assigned_user_id' => $this->agent->id]);
        $unassigned = Ticket::factory()->create();

        $response = $this->actingAs($this->agent)->getJson('/api/v1/tickets/queues/mine');

        $response->assertOk();
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals($assigned->uuid, $data[0]['id']);
    }

    public function test_personal_queue_empty_returns_collection_envelope(): void
    {
        $response = $this->actingAs($this->agent)->getJson('/api/v1/tickets/queues/mine');

        $response->assertOk();
        $response->assertJsonStructure([
            'data',
            'meta' => ['page', 'per_page', 'total', 'total_pages'],
        ]);
        $this->assertCount(0, $response->json('data'));
    }

    public function test_personal_queue_respects_pagination(): void
    {
        for ($i = 0; $i < 15; $i++) {
            Ticket::factory()->create(['assigned_user_id' => $this->agent->id]);
        }

        $response = $this->actingAs($this->agent)->getJson('/api/v1/tickets/queues/mine?per_page=5');

        $response->assertOk();
        $this->assertCount(5, $response->json('data'));
        $this->assertEquals(15, $response->json('meta.total'));
    }

    public function test_personal_queue_respects_sort(): void
    {
        $ticket1 = Ticket::factory()->create(['assigned_user_id' => $this->agent->id, 'priority' => 1]);
        $ticket2 = Ticket::factory()->create(['assigned_user_id' => $this->agent->id, 'priority' => 2]);

        $response = $this->actingAs($this->agent)->getJson('/api/v1/tickets/queues/mine?sort=-priority');

        $response->assertOk();
        $data = $response->json('data');
        $this->assertEquals($ticket2->uuid, $data[0]['id']);
        $this->assertEquals($ticket1->uuid, $data[1]['id']);
    }

    public function test_department_queue_returns_tickets_in_department(): void
    {
        $department = $this->agent->departments()->first();
        $assigned = Ticket::factory()->create([
            'department_id' => $department->id,
            'assigned_user_id' => $this->agent->id,
        ]);
        $unassigned = Ticket::factory()->create(['department_id' => $department->id]);
        $other = Ticket::factory()->create();

        $response = $this->actingAs($this->agent)->getJson(
            "/api/v1/tickets/queues/department/{$department->id}"
        );

        $response->assertOk();
        $data = $response->json('data');
        $uuids = array_map(fn ($t) => $t['id'], $data);
        $this->assertContains($assigned->uuid, $uuids);
        $this->assertContains($unassigned->uuid, $uuids);
        $this->assertNotContains($other->uuid, $uuids);
    }

    public function test_department_queue_respects_pagination(): void
    {
        $department = $this->agent->departments()->first();
        for ($i = 0; $i < 15; $i++) {
            Ticket::factory()->create(['department_id' => $department->id]);
        }

        $response = $this->actingAs($this->agent)->getJson(
            "/api/v1/tickets/queues/department/{$department->id}?per_page=10"
        );

        $response->assertOk();
        $this->assertCount(10, $response->json('data'));
        $this->assertEquals(15, $response->json('meta.total'));
    }

    public function test_department_queue_requires_permission(): void
    {
        $userWithoutPermission = User::factory()->create();
        $department = $userWithoutPermission->departments()->first() ?? Ticket::factory()->create()->department;

        $response = $this->actingAs($userWithoutPermission)->getJson(
            "/api/v1/tickets/queues/department/{$department->id}"
        );

        $response->assertForbidden();
    }
}
