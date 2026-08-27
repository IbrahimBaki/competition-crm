<?php

namespace App\Domains\Automation\Actions;

use App\Domains\Automation\Models\AutomationRule;
use App\Domains\Security\Services\AuditLogger;
use App\Models\User;

class DeleteAutomationRule
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
    ) {}

    public function handle(AutomationRule $rule, User $actor): void
    {
        $before = $rule->toArray();

        $rule->delete();

        $this->auditLogger->record(
            $actor,
            'automation.rules.delete',
            $rule,
            $before,
            []
        );
    }
}
