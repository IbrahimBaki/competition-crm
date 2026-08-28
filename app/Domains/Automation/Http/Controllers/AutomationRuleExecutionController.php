<?php

namespace App\Domains\Automation\Http\Controllers;

use App\Domains\Automation\Http\Resources\AutomationRuleExecutionResource;
use App\Domains\Automation\Models\AutomationRuleExecution;

class AutomationRuleExecutionController
{
    public function index()
    {
        $query = AutomationRuleExecution::query();

        if (request('ticket')) {
            $query->where('ticket_id', request('ticket'));
        }

        if (request('rule')) {
            $query->where('automation_rule_id', request('rule'));
        }

        return AutomationRuleExecutionResource::collection(
            $query->paginate(request('per_page', 25))
        );
    }
}
