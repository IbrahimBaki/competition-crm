<?php

namespace App\Domains\Ai\Actions;

use App\Domains\Ai\Exceptions\AiSuggestionAlreadyResolvedException;
use App\Domains\Ai\Models\AiSuggestion;
use App\Domains\Ai\Models\AiSuggestionState;
use App\Domains\Security\Services\AuditLogger;
use App\Models\User;

class ResolveAiSuggestion
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
    ) {}

    public function handle(AiSuggestion $suggestion, User $actor, string $decision): void
    {
        if ($suggestion->state !== AiSuggestionState::Pending) {
            throw new AiSuggestionAlreadyResolvedException('This suggestion has already been resolved');
        }

        $newState = match ($decision) {
            'accept' => AiSuggestionState::Accepted,
            'discard' => AiSuggestionState::Discarded,
            default => throw new \InvalidArgumentException("Invalid decision: {$decision}"),
        };

        $suggestion->update([
            'state' => $newState,
            'resolved_by_user_id' => $actor->id,
            'resolved_at' => now(),
        ]);

        $this->auditLogger->record(
            $actor,
            "ai_suggestion_{$decision}",
            $suggestion,
            ['state' => AiSuggestionState::Pending->value],
            ['state' => $newState->value]
        );
    }
}
