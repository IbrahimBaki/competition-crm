<?php

namespace App\Domains\Ticketing\Services\Lifecycle;

use App\Domains\Ticketing\Models\TicketStatus;

final class TicketTransitionMap
{
    private const TRANSITIONS = [
        'new' => ['open', 'pending', 'resolved', 'spam'],
        'open' => ['pending', 'resolved', 'spam'],
        'pending' => ['open', 'resolved', 'spam'],
        'resolved' => ['open', 'closed', 'spam'],
        'closed' => ['spam'],
        'spam' => ['open'],
    ];

    private const REQUIRES_REASON = [
        'new->spam' => true,
        'open->spam' => true,
        'pending->spam' => true,
        'resolved->spam' => true,
        'closed->spam' => true,
        'resolved->open' => true,
        'spam->open' => true,
    ];

    public function allowedFrom(TicketStatus $from): array
    {
        $targets = self::TRANSITIONS[$from->value] ?? [];

        return array_map(fn ($v) => TicketStatus::from($v), $targets);
    }

    public function allows(TicketStatus $from, TicketStatus $to): bool
    {
        if ($from->value === $to->value) {
            return true;
        }

        $targets = self::TRANSITIONS[$from->value] ?? [];

        return in_array($to->value, $targets, true);
    }

    public function requiresReason(TicketStatus $from, TicketStatus $to): bool
    {
        if ($from->value === $to->value) {
            return false;
        }

        return self::REQUIRES_REASON["{$from->value}->{$to->value}"] ?? false;
    }
}
