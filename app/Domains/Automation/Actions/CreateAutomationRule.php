<?php

namespace App\Domains\Automation\Actions;

use App\Domains\Automation\Models\AutomationRule;
use App\Domains\Security\Services\AuditLogger;
use App\Models\User;

class CreateAutomationRule
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
    ) {}

    public function handle(array $data, User $actor): AutomationRule
    {
        $rule = AutomationRule::create($data);

        $this->auditLogger->record(
            $actor,
            'automation.rules.create',
            $rule,
            [],
            $rule->toArray()
        );

        return $rule;
    }
}
