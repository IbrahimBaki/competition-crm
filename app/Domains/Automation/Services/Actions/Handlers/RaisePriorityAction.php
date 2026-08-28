<?php

namespace App\Domains\Automation\Services\Actions\Handlers;

use App\Domains\Automation\Models\RuleActionType;
use App\Domains\Automation\Services\Actions\RuleAction;
use App\Domains\Ticketing\Actions\ChangeTicketPriority;
use App\Domains\Ticketing\Models\Ticket;
use App\Domains\Ticketing\Models\TicketPriority;
use App\Models\User;

final class RaisePriorityAction implements RuleAction
{
    public function __construct(
        private readonly ChangeTicketPriority $changePriority,
    ) {}

    public function type(): RuleActionType
    {
        return RuleActionType::RaisePriority;
    }

    public function execute(Ticket $ticket, array $config, ?User $actor): array
    {
        $targetPriority = $config['priority'] ?? null;
        if (! $targetPriority) {
            return [];
        }

        $target = TicketPriority::tryFrom($targetPriority);
        if (! $target) {
            return [];
        }

        if ($ticket->priority->value <= $target->value) {
            return [];
        }

        $before = ['priority' => $ticket->priority->value];
        $ticket = $this->changePriority->handle($ticket, $target, $actor);
        $after = ['priority' => $ticket->priority->value];

        return ['before' => $before, 'after' => $after];
    }
}
