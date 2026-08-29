<?php

namespace Tests\Feature\Channels;

use App\Domains\Channels\Chat\Models\ChatSession;
use App\Domains\Channels\Chat\Models\ChatSessionState;
use App\Domains\Channels\Chat\Models\ChatVisitorIdentity;
use App\Domains\Organisation\Models\Branch;
use App\Domains\Organisation\Models\Department;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * The visitor-facing (public, unauthenticated) side of the chat widget:
 * fetching a transcript, sending a message, ending a session — none of
 * which had routes before this story.
 */
class PublicChatSessionHttpTest extends TestCase
{
    use RefreshDatabase;

    private function createSessionWithVisitor(string $visitorToken): ChatSession
    {
        $branch = Branch::create([
            'id' => (string) Str::uuid(),
            'name' => ['ar' => 'الفرع', 'en' => 'Branch'],
            'code' => 'wf-branch',
        ]);
        $department = Department::create([
            'id' => (string) Str::uuid(),
            'branch_id' => $branch->id,
            'name' => ['ar' => 'قسم', 'en' => 'Dept'],
            'code' => 'wf-dept',
            'is_active' => true,
        ]);
        $visitor = ChatVisitorIdentity::factory()->create(['visitor_token' => $visitorToken]);

        return ChatSession::create([
            'chat_visitor_identity_id' => $visitor->id,
            'department_id' => $department->id,
            'branch_id' => $branch->id,
            'state' => ChatSessionState::Active->value,
            'queued_at' => now(),
            'activated_at' => now(),
        ]);
    }

    public function test_creating_a_session_returns_201(): void
    {
        $branch = Branch::create([
            'id' => (string) Str::uuid(),
            'name' => ['ar' => 'الفرع', 'en' => 'Branch'],
            'code' => 'create-branch',
        ]);
        $department = Department::create([
            'id' => (string) Str::uuid(),
            'branch_id' => $branch->id,
            'name' => ['ar' => 'قسم', 'en' => 'Dept'],
            'code' => 'create-dept',
            'is_active' => true,
        ]);

        // Regression guard: ApiResponse::item(...)->toResponse($request, 201)
        // silently returned 200 because toResponse() takes no status argument.
        $response = $this->postJson('/api/v1/channels/chat/sessions', [
            'department' => $department->id,
        ]);

        $response->assertCreated();
        $this->assertNotEmpty($response->json('data.visitor_token'));
    }

    public function test_public_department_discovery_exposes_only_active_names_and_ids(): void
    {
        $active = Department::factory()->create(['is_active' => true]);
        Department::factory()->create(['is_active' => false]);

        $response = $this->getJson('/api/v1/channels/public/chat/departments');

        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
        $response->assertJsonPath('data.0.id', (string) $active->id)
            ->assertJsonStructure(['data' => [['id', 'name']]]);
        $this->assertSame(['id', 'name'], array_keys($response->json('data.0')));
    }

    public function test_visitor_can_fetch_transcript_with_correct_token(): void
    {
        $token = Str::random(32);
        $session = $this->createSessionWithVisitor($token);
        $session->messages()->create([
            'sequence' => 1,
            'author_type' => 'agent',
            'body' => "Hi, I'm Ahmed. How can I help?",
            'sent_at' => now(),
        ]);

        $response = $this->getJson("/api/v1/channels/public/chat/sessions/{$session->uuid}/messages?visitor_token={$token}");

        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
    }

    public function test_wrong_visitor_token_gets_404_not_403(): void
    {
        $session = $this->createSessionWithVisitor(Str::random(32));

        $response = $this->getJson("/api/v1/channels/public/chat/sessions/{$session->uuid}/messages?visitor_token=wrong-token");

        // 404, not 403: an attacker probing session ids must not be able to
        // distinguish "wrong token" from "session does not exist" — the same
        // rule attachments and guest ticket tracking already follow.
        $response->assertNotFound();
    }

    public function test_visitor_can_send_a_message(): void
    {
        $token = Str::random(32);
        $session = $this->createSessionWithVisitor($token);

        $response = $this->postJson("/api/v1/channels/public/chat/sessions/{$session->uuid}/messages", [
            'visitor_token' => $token,
            'body' => "I can't reset my password",
        ]);

        $response->assertCreated();
        $response->assertJsonPath('data.author_type', 'visitor');
    }

    public function test_visitor_cannot_send_a_message_to_someone_elses_session(): void
    {
        $session = $this->createSessionWithVisitor(Str::random(32));

        $response = $this->postJson("/api/v1/channels/public/chat/sessions/{$session->uuid}/messages", [
            'visitor_token' => 'not-the-real-token',
            'body' => 'Hello',
        ]);

        $response->assertNotFound();
    }

    public function test_visitor_can_end_the_session(): void
    {
        $token = Str::random(32);
        $session = $this->createSessionWithVisitor($token);

        $response = $this->postJson("/api/v1/channels/public/chat/sessions/{$session->uuid}/end", [
            'visitor_token' => $token,
        ]);

        $response->assertOk();
        $response->assertJsonPath('data.state', 'ended');
        $this->assertEquals('ended', $session->fresh()->state->value);
    }

    public function test_message_requires_visitor_token(): void
    {
        $session = $this->createSessionWithVisitor(Str::random(32));

        $this->postJson("/api/v1/channels/public/chat/sessions/{$session->uuid}/messages", [
            'body' => 'Hello',
        ])->assertStatus(422);
    }

    public function test_staff_transcript_route_and_public_transcript_route_do_not_collide(): void
    {
        // Regression guard for the class of bug fixed in Story: a public GET
        // registered on the same path as the staff GET made the staff route
        // unreachable. The two must resolve to different controllers.
        $router = app('router');
        $publicRoute = collect($router->getRoutes())->first(
            fn ($r) => $r->uri() === 'api/v1/channels/public/chat/sessions/{session}/messages' && in_array('GET', $r->methods())
        );
        $staffRoute = collect($router->getRoutes())->first(
            fn ($r) => $r->uri() === 'api/v1/channels/chat/sessions/{session}/messages' && in_array('GET', $r->methods())
        );

        $this->assertNotNull($publicRoute);
        $this->assertNotNull($staffRoute);
        $this->assertNotEquals($publicRoute->getActionName(), $staffRoute->getActionName());
    }
}
