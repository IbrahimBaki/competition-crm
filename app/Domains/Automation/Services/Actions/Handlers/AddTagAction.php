<?php

namespace App\Domains\Automation\Services\Actions\Handlers;

use App\Domains\Automation\Models\RuleActionType;
use App\Domains\Automation\Services\Actions\RuleAction;
use App\Domains\Ticketing\Actions\AddTicketTag;
use App\Domains\Ticketing\Models\Ticket;
use App\Models\User;

final class AddTagAction implements RuleAction
{
    public function __construct(
        private readonly AddTicketTag $addTag,
    ) {}

    public function type(): RuleActionType
    {
        return RuleActionType::AddTag;
    }

    public function execute(Ticket $ticket, array $config, ?User $actor): array
    {
        $tag = $config['tag'] ?? null;
        if (! $tag) {
            return [];
        }

        if ($ticket->tags()->where('name', $tag)->exists()) {
            return [];
        }

        $this->addTag->handle($ticket, $tag, $actor);

        return ['before' => [], 'after' => ['tag_added' => $tag]];
    }
}
