<?php

namespace App\Domains\Automation\Services\Actions;

use App\Domains\Automation\Models\RuleActionType;
use App\Domains\Ticketing\Models\Ticket;
use App\Models\User;

interface RuleAction
{
    public function type(): RuleActionType;

    /**
     * @return array<string, mixed>
     */
    public function execute(Ticket $ticket, array $config, ?User $actor): array;
}
