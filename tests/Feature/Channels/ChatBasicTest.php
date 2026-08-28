<?php

namespace Tests\Feature\Channels;

use App\Domains\Channels\Chat\Models\ChatSession;
use App\Domains\Channels\Chat\Models\ChatSessionState;
use App\Domains\Channels\Chat\Models\ChatVisitorIdentity;
use App\Domains\Channels\Chat\Services\Transcript\PersistChatTranscript;
use App\Domains\Organisation\Models\Branch;
use App\Domains\Organisation\Models\Department;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ChatBasicTest extends TestCase
{
    use RefreshDatabase;

    public function test_session_with_messages_creates_ticket_transcript()
    {
        $dept = $this->createDepartment();
        $session = $this->createChatSession($dept, ChatSessionState::Active);

        app(PostChatMessage::class)->handle($session, 'visitor', 'Hello');
        app(PostChatMessage::class)->handle($session, 'agent', 'Hi there');

        app(EndChatSession::class)->handle($session);

        $session->refresh();
        $this->assertNotNull($session->ticket_id);
        $this->assertTrue($session->messages()->count() > 0);
    }

    public function test_transcript_persistence_is_idempotent()
    {
        $dept = $this->createDepartment();
        $session = $this->createChatSession($dept, ChatSessionState::Active);

        app(PostChatMessage::class)->handle($session, 'visitor', 'Test message');
        app(EndChatSession::class)->handle($session);

        $firstTicketMessageCount = $session->refresh()->ticket->messages()->count();

        app(PersistChatTranscript::class)->handle($session);

        $secondTicketMessageCount = $session->refresh()->ticket->messages()->count();
        $this->assertEquals($firstTicketMessageCount, $secondTicketMessageCount);
    }

    public function test_abandoned_session_without_messages_creates_no_ticket()
    {
        $dept = $this->createDepartment();
        $session = $this->createChatSession($dept, ChatSessionState::Queued);

        app(AbandonChatSession::class)->handle($session);

        $session->refresh();
        $this->assertNull($session->ticket_id);
    }

    public function test_abandoned_session_with_messages_creates_transcript()
    {
        $dept = $this->createDepartment();
        $session = $this->createChatSession($dept, ChatSessionState::Active);

        app(PostChatMessage::class)->handle($session, 'visitor', 'Abandoned message');

        app(AbandonChatSession::class)->handle($session);

        $session->refresh();
        $this->assertNotNull($session->ticket_id);
    }

    private function createDepartment(): Department
    {
        return Department::create([
            'id' => Str::uuid(),
            'branch_id' => $this->getOrCreateBranch(),
            'name' => ['ar' => 'قسم', 'en' => 'Dept'],
            'code' => 'dept-test',
            'is_active' => true,
        ]);
    }

    private function getOrCreateBranch()
    {
        $branch = Branch::first();
        if ($branch) {
            return $branch->id;
        }

        return Branch::create([
            'id' => Str::uuid(),
            'name' => ['ar' => 'الفرع', 'en' => 'Branch'],
            'code' => 'test-branch',
        ])->id;
    }

    private function createChatSession(Department $dept, ChatSessionState $state): ChatSession
    {
        return ChatSession::create([
            'chat_visitor_identity_id' => $this->createVisitorIdentity()->id,
            'department_id' => $dept->id,
            'branch_id' => $dept->branch_id,
            'state' => $state->value,
            'requested_at' => now(),
        ]);
    }

    private function createVisitorIdentity()
    {
        return ChatVisitorIdentity::create([
            'id' => Str::uuid(),
            'visitor_token' => Str::random(32),
            'display_name' => 'Test Visitor',
        ]);
    }
}
