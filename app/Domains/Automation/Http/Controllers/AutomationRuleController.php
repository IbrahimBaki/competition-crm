<?php

namespace App\Domains\Automation\Http\Controllers;

use App\Domains\Automation\Http\Resources\AutomationRuleResource;
use App\Domains\Automation\Models\AutomationRule;

class AutomationRuleController
{
    public function index()
    {
        return AutomationRuleResource::collection(
            AutomationRule::paginate(request('per_page', 25))
        );
    }

    public function show(AutomationRule $rule)
    {
        return AutomationRuleResource::make($rule);
    }
}
