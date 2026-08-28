<?php

namespace App\Domains\Automation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAutomationRuleRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->user()->can('automation.rules.manage');
    }

    public function rules()
    {
        return [
            'key' => 'required|string|regex:/^[a-z0-9-]+$/|unique:automation_rules',
            'name' => 'required|array',
            'name.en' => 'required|string',
            'name.ar' => 'required|string',
            'trigger' => 'required|string',
            'priority' => 'integer|min:0|max:1000',
            'department_id' => 'nullable|exists:departments,id',
            'conditions' => 'array',
            'actions' => 'required|array',
            'escalation_level' => 'nullable|integer|min:1',
            'cooldown_minutes' => 'nullable|integer|min:1',
            'stop_on_match' => 'boolean',
            'is_active' => 'boolean',
        ];
    }
}
