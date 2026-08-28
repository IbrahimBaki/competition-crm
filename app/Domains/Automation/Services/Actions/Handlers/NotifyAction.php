<?php

namespace App\Domains\Automation\Services\Actions\Handlers;

use App\Domains\Automation\Models\RuleActionType;
use App\Domains\Automation\Services\Actions\RuleAction;
use App\Domains\Ticketing\Models\Ticket;
use App\Models\User;

final class NotifyAction implements RuleAction
{
    public function type(): RuleActionType
    {
        return RuleActionType::Notify;
    }

    public function execute(Ticket $ticket, array $config, ?User $actor): array
    {
        $channel = $config['channel'] ?? null;
        $recipient = $config['recipient'] ?? null;

        if (! $channel || ! $recipient) {
            return [];
        }

        return [
            'before' => [],
            'after' => [
                'notification_intent' => [
                    'channel' => $channel,
                    'recipient' => $recipient,
                ],
            ],
        ];
    }
}
