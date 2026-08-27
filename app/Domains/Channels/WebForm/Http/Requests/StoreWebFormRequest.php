<?php

namespace App\Domains\Channels\WebForm\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreWebFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'key' => ['required', 'string', 'unique:web_forms,key'],
            'title' => ['required', 'array'],
            'title.en' => ['required', 'string'],
            'title.ar' => ['required', 'string'],
            'description' => ['nullable', 'array'],
            'description.en' => ['nullable', 'string'],
            'description.ar' => ['nullable', 'string'],
            'department_id' => ['required', 'exists:departments,id'],
            'ticket_category_id' => ['nullable', 'exists:ticket_categories,id'],
            'default_priority' => ['required', 'string'],
        ];
    }
}
