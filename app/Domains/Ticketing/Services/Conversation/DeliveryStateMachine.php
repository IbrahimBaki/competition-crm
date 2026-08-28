<?php

namespace App\Domains\Ticketing\Services\Conversation;

use App\Domains\Ticketing\Models\MessageChannel;
use App\Domains\Ticketing\Models\MessageDeliveryState;

final class DeliveryStateMachine
{
    private const TRANSITIONS = [
        'queued' => ['sent', 'failed'],
        'sent' => ['delivered', 'read', 'failed'],
        'delivered' => ['read', 'failed'],
        'read' => [],
        'failed' => ['queued'],
    ];

    public function canTransition(
        MessageDeliveryState $from,
        MessageDeliveryState $to,
        ?MessageChannel $channel = null,
    ): bool {
        $allowed = self::TRANSITIONS[$from->value] ?? [];

        if (! in_array($to->value, $allowed, true)) {
            return false;
        }

        if ($to === MessageDeliveryState::Read && $channel && ! $channel->supportsReadReceipts()) {
            return false;
        }

        return true;
    }

    /**
     * @return list<MessageDeliveryState>
     */
    public function allowedFrom(MessageDeliveryState $from, ?MessageChannel $channel = null): array
    {
        $allowed = self::TRANSITIONS[$from->value] ?? [];

        $states = array_map(
            fn (string $value) => MessageDeliveryState::from($value),
            $allowed,
        );

        if ($channel && ! $channel->supportsReadReceipts()) {
            $states = array_filter(
                $states,
                fn (MessageDeliveryState $state) => $state !== MessageDeliveryState::Read,
            );
        }

        return array_values($states);
    }
}
