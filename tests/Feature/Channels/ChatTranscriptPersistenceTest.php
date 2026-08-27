<?php

namespace Tests\Feature\Channels;

use App\Domains\Channels\Chat\Actions\AbandonChatSession;
use App\Domains\Channels\Chat\Actions\EndChatSession;
use App\Domains\Channels\Chat\Actions\PostChatMessage;
use App\Domains\Channels\Chat\Models\ChatSession;
use App\Domains\Channels\Chat\Models\ChatSessionState;
use App\Domains\Channels\Chat\Services\Transcript\PersistChatTranscript;
use App\Domains\Organisation\Models\Department;
use App\Domains\Ticketing\Models\MessageChannel;

class ChatTranscriptPersistenceTest extends ChatTestCase
{
    public function test_session_with_messages_creates_ticket_transcript()
    {
        $dept = Department::factory()->create();
        $session = ChatSession::factory()->create(['department_id' => $dept->id, 'state' => ChatSessionState::Active->value]);

        app(PostChatMessage::class)->handle($session, 'visitor', 'Hello');
        app(PostChatMessage::class)->handle($session, 'agent', 'Hi there');

        app(EndChatSession::class)->handle($session);

        $this->assertNotNull($session->fresh()->ticket_id);
        $messages = $session->fresh()->ticket->messages()->where('channel', MessageChannel::Chat->value)->get();
        $this->assertCount(2, $messages);
        $this->assertEquals('Hello', $messages[0]->body);
        $this->assertEquals('Hi there', $messages[1]->body);
    }

    public function test_transcript_persistence_is_idempotent()
    {
        $dept = Department::factory()->create();
        $session = ChatSession::factory()->create(['department_id' => $dept->id, 'state' => ChatSessionState::Active->value]);

        app(PostChatMessage::class)->handle($session, 'visitor', 'Test message');
        app(EndChatSession::class)->handle($session);

        $firstCount = $session->fresh()->ticket->messages()->where('channel', MessageChannel::Chat->value)->count();

        app(PersistChatTranscript::class)->handle($session);

        $secondCount = $session->fresh()->ticket->messages()->where('channel', MessageChannel::Chat->value)->count();
        $this->assertEquals($firstCount, $secondCount);
    }

    public function test_abandoned_session_without_messages_creates_no_ticket()
    {
        $dept = Department::factory()->create();
        $session = ChatSession::factory()->create(['department_id' => $dept->id, 'state' => ChatSessionState::Queued->value]);

        app(AbandonChatSession::class)->handle($session);

        $this->assertNull($session->fresh()->ticket_id);
    }

    public function test_abandoned_session_with_messages_creates_transcript()
    {
        $dept = Department::factory()->create();
        $session = ChatSession::factory()->create(['department_id' => $dept->id, 'state' => ChatSessionState::Active->value]);

        app(PostChatMessage::class)->handle($session, 'visitor', 'Abandoned message');

        app(AbandonChatSession::class)->handle($session);

        $this->assertNotNull($session->fresh()->ticket_id);
    }
}
