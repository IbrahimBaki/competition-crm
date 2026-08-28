<?php

namespace Tests\Feature\Workspace;

use App\Domains\Security\Permissions\PermissionKey;
use App\Domains\Workspace\Models\AgentTask;
use App\Models\User;
use App\Support\Http\Errors\ErrorCode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Story467IntegrationTest extends TestCase
{
    use RefreshDatabase;

    private User $agent;

    protected function setUp(): void
    {
        parent::setUp();
        $this->agent = User::factory()->create();
    }

    public function test_task_has_owner_optional_ticket_link_due_instant_state_history(): void
    {
        $response = $this->actingAs($this->agent)
            ->postJson('/api/v1/agent-tasks', [
                'owner_id' => $this->agent->uuid,
                'title' => 'Test',
                'due_at' => now()->addDay()->toIso8601String(),
            ]);

        $response->assertCreated();
        $data = $response->json('data');
        $this->assertNotNull($data['owner']);
        $this->assertNotNull($data['state']);
        $this->assertNotNull($data['due_at']);
        $this->assertNull($data['ticket']);
    }

    public function test_overdue_filter(): void
    {
        AgentTask::factory()->overdue()->create(['owner_id' => $this->agent->id]);
        AgentTask::factory()->create(['owner_id' => $this->agent->id]);

        $response = $this->actingAs($this->agent)->getJson('/api/v1/agent-tasks?overdue=true');

        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
        $this->assertTrue($response->json('data.0.is_overdue'));
    }

    public function test_task_state_transitions(): void
    {
        $task = AgentTask::factory()->create(['owner_id' => $this->agent->id]);

        $response = $this->actingAs($this->agent)
            ->postJson("/api/v1/agent-tasks/{$task->uuid}/state", ['state' => 'in_progress']);

        $response->assertOk();
        $this->assertEquals('in_progress', $response->json('data.state'));

        $response = $this->actingAs($this->agent)
            ->postJson("/api/v1/agent-tasks/{$task->uuid}/state", ['state' => 'done']);

        $response->assertOk();
        $this->assertEquals('done', $response->json('data.state'));
        $this->assertNotNull($response->json('data.completed_at'));
    }

    public function test_permission_keys_exist(): void
    {
        $permKeys = PermissionKey::all();
        $this->assertContains('workspace.tasks.create', $permKeys);
        $this->assertContains('workspace.tasks.view.own', $permKeys);
        $this->assertContains('workspace.quick_replies.manage.shared', $permKeys);
    }

    public function test_error_codes_exist(): void
    {
        $codes = ErrorCode::cases();
        $codeValues = array_map(fn ($c) => $c->value, $codes);

        $this->assertContains('agent_task.illegal_transition', $codeValues);
        $this->assertContains('quick_reply.title_taken', $codeValues);
        $this->assertContains('mention.not_allowed_on_public_reply', $codeValues);
    }

    public function test_notification_event_types_exist(): void
    {
        $this->assertTrue(defined('App\Domains\Notifications\Enums\NotificationEventType::AgentTaskReminder'));
        $this->assertTrue(defined('App\Domains\Notifications\Enums\NotificationEventType::TicketMentioned'));
        $this->assertTrue(defined('App\Domains\Notifications\Enums\NotificationEventType::TicketWatchedUpdate'));
    }
}
