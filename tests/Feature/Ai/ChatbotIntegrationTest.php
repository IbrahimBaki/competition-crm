<?php

namespace Tests\Feature\Ai;

use App\Domains\Ai\Services\Chatbot\ChatbotIntegration;
use App\Domains\Channels\Chat\Models\ChatSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatbotIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_chatbot_label_is_bilingual(): void
    {
        $this->assertTrue(trans('chatbot.participant_label') !== 'chatbot.participant_label');
        $this->assertTrue(trans('chatbot.participant_label', locale: 'ar') !== 'chatbot.participant_label');
    }

    public function test_chatbot_handoff_message_is_bilingual(): void
    {
        $this->assertTrue(trans('chatbot.handoff_message') !== 'chatbot.handoff_message');
        $this->assertTrue(trans('chatbot.handoff_message', locale: 'ar') !== 'chatbot.handoff_message');
    }

    public function test_chatbot_disabled_does_not_respond(): void
    {
        config(['ai.enabled' => false]);

        $session = ChatSession::factory()->create();
        $integration = app(ChatbotIntegration::class);

        // Should not throw, just return without responding
        $integration->handleVisitorMessage($session, 'Hello');

        // No bot messages should be posted
        $botMessages = $session->messages()->where('author_type', 'bot')->count();
        $this->assertEquals(0, $botMessages);
    }
}
