<?php

namespace App\Domains\Knowledge\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateKnowledgeCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manage', $this->category);
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'array', 'size:2'],
            'name.ar' => ['required_with:name', 'string', 'max:255'],
            'name.en' => ['required_with:name', 'string', 'max:255'],
            'position' => ['sometimes', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
