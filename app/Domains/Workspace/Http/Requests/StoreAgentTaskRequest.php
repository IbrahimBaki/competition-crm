<?php

namespace App\Domains\Workspace\Http\Requests;

use App\Domains\Workspace\Models\AgentTask;
use Illuminate\Foundation\Http\FormRequest;

class StoreAgentTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', AgentTask::class);
    }

    public function rules(): array
    {
        return [
            'owner_id' => 'required|uuid|exists:users,uuid',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_at' => 'nullable|date_format:c',
            'due_in_working_time' => 'boolean',
            'branch_id' => 'nullable|uuid|exists:branches,uuid',
            'ticket_id' => 'nullable|uuid|exists:tickets,uuid',
        ];
    }
}
