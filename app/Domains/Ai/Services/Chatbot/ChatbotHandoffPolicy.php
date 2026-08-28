<?php

namespace App\Domains\Ai\Services\Chatbot;

use App\Domains\Channels\Chat\Models\ChatSession;
use App\Domains\Channels\Chat\Models\ChatSessionEventType;
use App\Domains\Channels\Chat\Services\Lifecycle\ChatSessionStateMachine;

class ChatbotHandoffPolicy
{
    private array $humanPhrases = [
        'human', 'agent', 'person', 'help', 'support',
        'بشري', 'موظف', 'شخص', 'مساعدة', 'دعم',
    ];

    public function shouldHandoff(
        ChatSession $session,
        string $visitorMessage,
        int $failureStreak = 0,
        bool $providerUnavailable = false,
    ): bool {
        // Explicit human request
        $lowercase = mb_strtolower($visitorMessage, 'UTF-8');
        foreach ($this->humanPhrases as $phrase) {
            if (stripos($lowercase, $phrase) !== false) {
                return true;
            }
        }

        // Too many failed turns
        if ($failureStreak >= config('ai.chatbot.max_failed_turns')) {
            return true;
        }

        // Provider unavailable
        if ($providerUnavailable) {
            return true;
        }

        // Feature disabled mid-session
        if (! config('ai.enabled') || ! config('ai.features.chatbot')) {
            return true;
        }

        return false;
    }

    public function executeHandoff(ChatSession $session): void
    {
        // Use existing transfer mechanism
        $stateMachine = app(ChatSessionStateMachine::class);
        $session->events()->create([
            'event_type' => ChatSessionEventType::Transferred,
            'metadata' => ['initiated_by' => 'chatbot_handoff'],
        ]);
    }
}
