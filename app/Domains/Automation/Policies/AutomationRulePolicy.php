<?php

namespace App\Domains\Automation\Policies;

use App\Domains\Automation\Models\AutomationRule;
use App\Models\User;

class AutomationRulePolicy
{
    public function viewAny(User $user)
    {
        return $user->hasPermission('automation.rules.view');
    }

    public function view(User $user, AutomationRule $rule)
    {
        return $user->hasPermission('automation.rules.view');
    }

    public function create(User $user)
    {
        return $user->hasPermission('automation.rules.manage');
    }

    public function update(User $user, AutomationRule $rule)
    {
        return $user->hasPermission('automation.rules.manage');
    }

    public function delete(User $user, AutomationRule $rule)
    {
        return $user->hasPermission('automation.rules.manage');
    }
}
