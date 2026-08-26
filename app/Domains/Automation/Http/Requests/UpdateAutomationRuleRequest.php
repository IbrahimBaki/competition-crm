<?php

namespace App\Domains\Automation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAutomationRuleRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->user()->can('automation.rules.manage');
    }

    public function rules()
    {
        return [
            'key' => 'string|regex:/^[a-z0-9-]+$/|unique:automation_rules,key,'.$this->route('rule')->id,
            'name' => 'array',
            'name.en' => 'string',
            'name.ar' => 'string',
            'trigger' => 'string',
            'priority' => 'integer|min:0|max:1000',
            'department_id' => 'nullable|exists:departments,id',
            'conditions' => 'array',
            'actions' => 'array',
            'escalation_level' => 'nullable|integer|min:1',
            'cooldown_minutes' => 'nullable|integer|min:1',
            'stop_on_match' => 'boolean',
            'is_active' => 'boolean',
        ];
    }
}
