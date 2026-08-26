<?php

namespace App\Domains\Automation\Services\Actions\Handlers;

use App\Domains\Automation\Models\RuleActionType;
use App\Domains\Automation\Services\Actions\RuleAction;
use App\Domains\Ticketing\Actions\AssignTicket;
use App\Domains\Ticketing\Models\Ticket;
use App\Models\User;

final class ReassignAction implements RuleAction
{
    public function __construct(
        private readonly AssignTicket $assignTicket,
    ) {}

    public function type(): RuleActionType
    {
        return RuleActionType::Reassign;
    }

    public function execute(Ticket $ticket, array $config, ?User $actor): array
    {
        $userId = $config['user_id'] ?? null;
        if (! $userId) {
            return [];
        }

        $user = User::find($userId);
        if (! $user) {
            return [];
        }

        $before = ['assigned_user_id' => $ticket->assigned_user_id];
        $ticket = $this->assignTicket->handle($ticket, $user, $actor, null);
        $after = ['assigned_user_id' => $ticket->assigned_user_id];

        return ['before' => $before, 'after' => $after];
    }
}
