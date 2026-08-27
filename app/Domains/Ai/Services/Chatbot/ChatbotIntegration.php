<?php

namespace App\Domains\Ai\Services\Chatbot;

use App\Domains\Ai\Exceptions\AiProviderUnavailableException;
use App\Domains\Channels\Chat\Actions\PostChatMessage;
use App\Domains\Channels\Chat\Actions\TransferChatSession;
use App\Domains\Channels\Chat\Models\ChatParticipantType;
use App\Domains\Channels\Chat\Models\ChatSession;
use Psr\Log\LoggerInterface;

class ChatbotIntegration
{
    public function __construct(
        private readonly ChatbotAnswerer $answerer,
        private readonly ChatbotHandoffPolicy $handoffPolicy,
        private readonly PostChatMessage $postMessage,
        private readonly TransferChatSession $transferSession,
        private readonly LoggerInterface $logger,
    ) {}

    public function handleVisitorMessage(ChatSession $session, string $visitorMessage): void
    {
        if (! config('ai.enabled') || ! config('ai.features.chatbot')) {
            return;
        }

        try {
            // Check if handoff is needed
            if ($this->handoffPolicy->shouldHandoff($session, $visitorMessage)) {
                $this->performHandoff($session);

                return;
            }

            // Try to answer using chatbot
            $answer = $this->answerer->answer($session, $visitorMessage);

            if ($answer === null) {
                $this->performHandoff($session);

                return;
            }

            // Post bot answer with bilingual label
            $label = __('chatbot.participant_label');
            $this->postMessage->handle($session, ChatParticipantType::Bot->value, "{$label}: {$answer}");
        } catch (AiProviderUnavailableException $e) {
            $this->logger->warning('Chatbot provider unavailable, handing off to human', ['session_id' => $session->id]);
            $this->performHandoff($session);
        } catch (\Throwable $e) {
            $this->logger->error('Chatbot error, handing off to human', ['session_id' => $session->id, 'error' => $e->getMessage()]);
            $this->performHandoff($session);
        }
    }

    private function performHandoff(ChatSession $session): void
    {
        try {
            $message = __('chatbot.handoff_message');
            $this->postMessage->handle($session, ChatParticipantType::Bot->value, $message);
            $this->handoffPolicy->executeHandoff($session);
        } catch (\Throwable $e) {
            $this->logger->error('Failed to hand off chat session', ['session_id' => $session->id, 'error' => $e->getMessage()]);
        }
    }
}
