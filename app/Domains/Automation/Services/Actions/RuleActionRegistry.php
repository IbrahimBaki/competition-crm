<?php

namespace App\Domains\Automation\Services\Actions;

use App\Domains\Automation\Exceptions\UnsupportedRuleActionException;

final class RuleActionRegistry
{
    /**
     * @var array<string, RuleAction>
     */
    private array $actions = [];

    public function register(RuleAction $action): self
    {
        $this->actions[$action->type()->value] = $action;

        return $this;
    }

    public function resolve(string $type): RuleAction
    {
        if (! isset($this->actions[$type])) {
            throw new UnsupportedRuleActionException("Unsupported action type: {$type}");
        }

        return $this->actions[$type];
    }
}
