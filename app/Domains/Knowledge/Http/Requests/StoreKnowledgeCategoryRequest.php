<?php

namespace App\Domains\Knowledge\Http\Requests;

use App\Domains\Knowledge\Models\KnowledgeCategory;
use Illuminate\Foundation\Http\FormRequest;

class StoreKnowledgeCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manage', KnowledgeCategory::class);
    }

    public function rules(): array
    {
        return [
            'parent_id' => ['nullable', 'exists:knowledge_categories,id'],
            'code' => ['required', 'string', 'unique:knowledge_categories,code'],
            'name' => ['required', 'array', 'size:2'],
            'name.ar' => ['required', 'string', 'max:255'],
            'name.en' => ['required', 'string', 'max:255'],
            'position' => ['sometimes', 'integer', 'min:0'],
        ];
    }
}
