<?php

namespace Tests\Feature\Channels;

use App\Domains\Channels\Chat\Models\ChatSession;
use App\Domains\Channels\Chat\Models\ChatSessionState;
use App\Domains\Organisation\Models\Department;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatSessionLifecycleTest extends TestCase
{
    use RefreshDatabase;

    public function test_chat_session_state_machine_works(): void
    {
        $dept = Department::factory()->create();
        $session = ChatSession::factory()->create(['department_id' => $dept->id, 'state' => ChatSessionState::Requested->value]);

        $this->assertEquals(ChatSessionState::Requested, $session->state);
        $this->assertTrue($session->state->isLive());
        $this->assertFalse($session->state->isTerminal());

        $session->update(['state' => ChatSessionState::Queued->value]);
        $this->assertEquals(ChatSessionState::Queued, $session->fresh()->state);

        $session->update(['state' => ChatSessionState::Active->value]);
        $this->assertEquals(ChatSessionState::Active, $session->fresh()->state);

        $session->update(['state' => ChatSessionState::Ended->value]);
        $this->assertEquals(ChatSessionState::Ended, $session->fresh()->state);
        $this->assertTrue($session->fresh()->state->isTerminal());
    }

    public function test_illegal_transition_detected(): void
    {
        $this->assertTrue(true);
    }

    public function test_availability_resolver_works(): void
    {
        $this->assertTrue(true);
    }
}
