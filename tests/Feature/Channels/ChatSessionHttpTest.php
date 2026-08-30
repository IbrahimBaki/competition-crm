<?php

namespace Tests\Feature\Channels;

use App\Domains\Channels\Chat\Models\ChatSession;
use App\Domains\Channels\Chat\Models\ChatSessionState;
use App\Domains\Channels\Chat\Models\ChatVisitorIdentity;
use App\Domains\Customers\Models\Customer;
use App\Domains\Organisation\Models\Branch;
use App\Domains\Organisation\Models\Department;
use App\Domains\Security\Permissions\PermissionKey;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\Support\InteractsWithPermissions;
use Tests\TestCase;

/**
 * The HTTP layer for the staff live-chat console: list, accept, send,
 * transfer, end. Everything here was previously undocumented AND unbuilt —
 * these tests are the first proof the routes, policy and actions agree.
 */
class ChatSessionHttpTest extends TestCase
{
    use InteractsWithPermissions;
    use RefreshDatabase;

    private Department $department;

    protected function setUp(): void
    {
        parent::setUp();

        $branch = Branch::create([
            'id' => (string) Str::uuid(),
            'name' => ['ar' => 'الفرع', 'en' => 'Branch'],
            'code' => 'test-branch',
        ]);

        $this->department = Department::create([
            'id' => (string) Str::uuid(),
            'branch_id' => $branch->id,
            'name' => ['ar' => 'قسم', 'en' => 'Dept'],
            'code' => 'test-dept',
            'is_active' => true,
        ]);
    }

    private function createSession(ChatSessionState $state = ChatSessionState::Queued): ChatSession
    {
        // Linked to a resolved Customer, matching a visitor who gave contact
        // info — see test_accept_without_a_resolved_customer_is_rejected for
        // the fully-anonymous case.
        $visitor = ChatVisitorIdentity::factory()->create([
            'customer_id' => Customer::factory()->create()->id,
        ]);

        return ChatSession::create([
            'chat_visitor_identity_id' => $visitor->id,
            'department_id' => $this->department->id,
            'branch_id' => $this->department->branch_id,
            'state' => $state->value,
            'queued_at' => now(),
        ]);
    }

    private function agentInDepartment(): User
    {
        $agent = User::factory()->create();
        $agent->departments()->attach($this->department->id);
        $this->grantPermission($agent, PermissionKey::CHANNELS_CHAT_VIEW, PermissionKey::CHANNELS_CHAT_ACCEPT);

        return $agent;
    }

    public function test_agent_without_permission_cannot_list_sessions(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->getJson('/api/v1/channels/chat/sessions')->assertForbidden();
    }

    public function test_agent_sees_only_sessions_in_their_department(): void
    {
        $agent = $this->agentInDepartment();
        $inScope = $this->createSession();

        $otherDept = Department::create([
            'id' => (string) Str::uuid(),
            'branch_id' => $this->department->branch_id,
            'name' => ['ar' => 'قسم آخر', 'en' => 'Other Dept'],
            'code' => 'other-dept',
            'is_active' => true,
        ]);
        ChatSession::create([
            'chat_visitor_identity_id' => ChatVisitorIdentity::factory()->create()->id,
            'department_id' => $otherDept->id,
            'branch_id' => $otherDept->branch_id,
            'state' => ChatSessionState::Queued->value,
            'queued_at' => now(),
        ]);

        $response = $this->actingAs($agent)->getJson('/api/v1/channels/chat/sessions');

        $response->assertOk();
        $ids = collect($response->json('data'))->pluck('id');
        $this->assertTrue($ids->contains($inScope->uuid));
        $this->assertCount(1, $ids);
    }

    public function test_agent_can_accept_a_queued_session_and_a_ticket_is_created(): void
    {
        $agent = $this->agentInDepartment();
        $session = $this->createSession();

        $response = $this->actingAs($agent)->postJson("/api/v1/channels/chat/sessions/{$session->uuid}/accept");

        $response->assertOk();
        $response->assertJsonPath('data.state', 'active');
        $session->refresh();
        $this->assertEquals($agent->uuid, $session->assigned_user_id);
        $this->assertNotNull($session->ticket_id);
    }

    /**
     * The widget's visitor-info step is optional (docs/ui/13-public-surfaces.md),
     * so a session can have no resolved Customer at all. Accepting it used to
     * crash with a raw TypeError from CreateTicket; it must now fail cleanly.
     */
    public function test_accepting_a_session_with_no_resolved_customer_is_rejected(): void
    {
        $agent = $this->agentInDepartment();
        $anonymousVisitor = ChatVisitorIdentity::factory()->create(['customer_id' => null]);
        $session = ChatSession::create([
            'chat_visitor_identity_id' => $anonymousVisitor->id,
            'department_id' => $this->department->id,
            'branch_id' => $this->department->branch_id,
            'state' => ChatSessionState::Queued->value,
            'queued_at' => now(),
        ]);

        $response = $this->actingAs($agent)->postJson("/api/v1/channels/chat/sessions/{$session->uuid}/accept");

        $response->assertStatus(422);
        $this->assertEquals('chat.visitor_contact_required', $response->json('error.code'));
    }

    public function test_accepting_an_already_active_session_is_rejected(): void
    {
        $agent = $this->agentInDepartment();
        $session = $this->createSession(ChatSessionState::Active);

        $response = $this->actingAs($agent)->postJson("/api/v1/channels/chat/sessions/{$session->uuid}/accept");

        $response->assertStatus(422);
        $this->assertEquals('chat.illegal_transition', $response->json('error.code'));
    }

    public function test_agent_at_capacity_cannot_accept_another_session(): void
    {
        $agent = $this->agentInDepartment();

        for ($i = 0; $i < 3; $i++) {
            $active = $this->createSession(ChatSessionState::Active);
            $active->update(['assigned_user_id' => $agent->uuid]);
        }

        $fourth = $this->createSession();

        $response = $this->actingAs($agent)->postJson("/api/v1/channels/chat/sessions/{$fourth->uuid}/accept");

        $response->assertStatus(409);
        $this->assertEquals('chat.agent_at_capacity', $response->json('error.code'));
    }

    public function test_only_the_assigned_agent_can_send_a_message(): void
    {
        $agent = $this->agentInDepartment();
        $otherAgent = $this->agentInDepartment();
        $session = $this->createSession(ChatSessionState::Active);
        $session->update(['assigned_user_id' => $agent->uuid]);

        $this->actingAs($otherAgent)
            ->postJson("/api/v1/channels/chat/sessions/{$session->uuid}/messages", ['body' => 'Hi'])
            ->assertForbidden();

        $this->actingAs($agent)
            ->postJson("/api/v1/channels/chat/sessions/{$session->uuid}/messages", ['body' => 'Hi'])
            ->assertCreated()
            ->assertJsonPath('data.body', 'Hi')
            ->assertJsonPath('data.author_type', 'agent');
    }

    public function test_message_over_max_length_is_rejected(): void
    {
        $agent = $this->agentInDepartment();
        $session = $this->createSession(ChatSessionState::Active);
        $session->update(['assigned_user_id' => $agent->uuid]);

        $this->actingAs($agent)
            ->postJson("/api/v1/channels/chat/sessions/{$session->uuid}/messages", [
                'body' => str_repeat('a', 5000),
            ])
            ->assertStatus(422);
    }

    public function test_transfer_requires_a_reason(): void
    {
        $agent = $this->agentInDepartment();
        $this->grantPermission($agent, PermissionKey::CHANNELS_CHAT_TRANSFER);
        $session = $this->createSession(ChatSessionState::Active);
        $session->update(['assigned_user_id' => $agent->uuid]);

        $this->actingAs($agent)
            ->postJson("/api/v1/channels/chat/sessions/{$session->uuid}/transfer", [
                'target_type' => 'queue',
            ])
            ->assertStatus(422);
    }

    public function test_agent_can_transfer_session_to_queue(): void
    {
        $agent = $this->agentInDepartment();
        $this->grantPermission($agent, PermissionKey::CHANNELS_CHAT_TRANSFER);
        $session = $this->createSession(ChatSessionState::Active);
        $session->update(['assigned_user_id' => $agent->uuid]);

        $response = $this->actingAs($agent)->postJson("/api/v1/channels/chat/sessions/{$session->uuid}/transfer", [
            'target_type' => 'queue',
            'reason' => 'Escalating to specialist team',
        ]);

        $response->assertOk();
        $response->assertJsonPath('data.state', 'queued');
    }

    public function test_ending_a_session_requires_manage_permission(): void
    {
        $agent = $this->agentInDepartment();
        $session = $this->createSession(ChatSessionState::Active);
        $session->update(['assigned_user_id' => $agent->uuid]);

        $this->actingAs($agent)
            ->postJson("/api/v1/channels/chat/sessions/{$session->uuid}/end")
            ->assertForbidden();

        $this->grantPermission($agent, PermissionKey::CHANNELS_CHAT_MANAGE);

        $this->actingAs($agent)
            ->postJson("/api/v1/channels/chat/sessions/{$session->uuid}/end")
            ->assertOk()
            ->assertJsonPath('data.state', 'ended');
    }

    public function test_agent_can_view_session_transcript(): void
    {
        $agent = $this->agentInDepartment();
        $session = $this->createSession(ChatSessionState::Active);
        $session->update(['assigned_user_id' => $agent->uuid]);
        $session->messages()->create([
            'sequence' => 1,
            'author_type' => 'visitor',
            'body' => 'Hello',
            'sent_at' => now(),
        ]);

        $response = $this->actingAs($agent)->getJson("/api/v1/channels/chat/sessions/{$session->uuid}/messages");

        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
    }
}
