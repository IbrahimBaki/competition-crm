<?php

namespace App\Domains\Workspace\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAgentTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'string|max:255',
            'description' => 'nullable|string',
            'due_at' => 'nullable|date_format:c',
        ];
    }
}
