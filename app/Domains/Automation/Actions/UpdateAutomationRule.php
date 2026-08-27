<?php

namespace App\Domains\Automation\Actions;

use App\Domains\Automation\Models\AutomationRule;
use App\Domains\Security\Services\AuditLogger;
use App\Models\User;

class UpdateAutomationRule
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
    ) {}

    public function handle(AutomationRule $rule, array $data, User $actor): AutomationRule
    {
        $before = $rule->toArray();

        $rule->update($data);

        $this->auditLogger->record(
            $actor,
            'automation.rules.update',
            $rule,
            $before,
            $rule->fresh()->toArray()
        );

        return $rule;
    }
}
