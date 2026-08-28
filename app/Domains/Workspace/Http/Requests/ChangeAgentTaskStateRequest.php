<?php

namespace App\Domains\Workspace\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ChangeAgentTaskStateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['state' => 'required|in:open,in_progress,done,cancelled'];
    }
}
