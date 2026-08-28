<?php

namespace Tests\Feature\Workspace;

use App\Domains\Workspace\Models\AgentTask;
use App\Models\User;
use Tests\TestCase;

class AgentTaskApiTest extends TestCase
{
    private User $agent;

    protected function setUp(): void
    {
        parent::setUp();
        $this->agent = User::factory()->create();
    }

    public function test_create_agent_task(): void
    {
        $response = $this->actingAs($this->agent)
            ->postJson('/api/v1/agent-tasks', [
                'owner_id' => $this->agent->uuid,
                'title' => 'Test Task',
                'description' => 'Test Description',
                'due_at' => now()->addDays(1)->toIso8601String(),
            ]);

        $response->assertCreated();
        $response->assertHasKey('data');
        $this->assertArrayHasKey('uuid', $response['data']);
        $this->assertArrayNotHasKey('id', $response['data']);
    }

    public function test_list_agent_tasks(): void
    {
        AgentTask::factory()->create(['owner_id' => $this->agent->id]);

        $response = $this->actingAs($this->agent)
            ->getJson('/api/v1/agent-tasks');

        $response->assertOk();
        $response->assertJsonStructure(['data' => [['uuid', 'title', 'state']]]);
    }

    public function test_filter_overdue_tasks(): void
    {
        AgentTask::factory()->overdue()->create(['owner_id' => $this->agent->id]);
        AgentTask::factory()->create(['owner_id' => $this->agent->id]);

        $response = $this->actingAs($this->agent)
            ->getJson('/api/v1/agent-tasks?overdue=true');

        $response->assertOk();
        $this->assertCount(1, $response['data']);
    }
}
