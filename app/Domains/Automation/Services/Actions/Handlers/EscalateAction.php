<?php

namespace App\Domains\Automation\Services\Actions\Handlers;

use App\Domains\Automation\Actions\EscalateTicket;
use App\Domains\Automation\Models\RuleActionType;
use App\Domains\Automation\Services\Actions\RuleAction;
use App\Domains\Ticketing\Models\Ticket;
use App\Models\User;

final class EscalateAction implements RuleAction
{
    public function __construct(
        private readonly EscalateTicket $escalate,
    ) {}

    public function type(): RuleActionType
    {
        return RuleActionType::Escalate;
    }

    public function execute(Ticket $ticket, array $config, ?User $actor): array
    {
        $level = $config['level'] ?? 1;
        $reason = $config['reason'] ?? 'Automatic escalation via rule';

        try {
            $this->escalate->handle($ticket, (int) $level, $reason, $actor);

            return ['before' => [], 'after' => ['escalated_to_level' => $level]];
        } catch (\Throwable $e) {
            return ['error' => $e->getMessage()];
        }
    }
}
