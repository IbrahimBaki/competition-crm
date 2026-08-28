<?php

namespace App\Domains\Workspace\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateQuickReplyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'array',
            'title.ar' => 'string|max:255',
            'title.en' => 'string|max:255',
            'body' => 'array',
            'body.ar' => 'string',
            'body.en' => 'string',
            'scope' => 'in:personal,shared',
            'is_active' => 'boolean',
            'department_id' => 'nullable|uuid|exists:departments,id',
        ];
    }
}
