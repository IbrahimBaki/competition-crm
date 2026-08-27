<?php

namespace App\Domains\Channels\Chat\Actions;

use App\Domains\Channels\Chat\Models\ChatSession;
use App\Domains\Channels\Chat\Models\ChatSessionState;
use App\Domains\Channels\Chat\Models\ChatTransferTargetType;
use App\Models\User;

final class TransferChatSession
{
    public function handle(ChatSession $session, ChatTransferTargetType $targetType, ?string $reason, ?User $targetAgent = null): ChatSession
    {
        if (! $reason || trim($reason) === '') {
            throw new \Exception('Transfer reason required');
        }

        $session->update(['state' => ChatSessionState::Transferred->value, 'transferred_at' => now()]);

        if ($targetType->value === 'agent') {
            $session->update(['assigned_user_id' => $targetAgent->uuid]);
        } elseif ($targetType->value === 'bot') {
            $session->update(['assigned_user_id' => null, 'handled_by' => 'bot']);
        } elseif ($targetType->value === 'queue') {
            $session->update(['state' => ChatSessionState::Queued->value, 'queued_at' => now(), 'assigned_user_id' => null]);
        }

        $session->events()->create(['type' => 'transferred', 'reason' => $reason, 'meta' => ['target' => $targetType->value]]);

        return $session->fresh();
    }
}
