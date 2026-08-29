<?php

namespace App\Domains\Knowledge\Http\Requests;

use App\Domains\Knowledge\Models\ArticleVisibility;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateKnowledgeArticleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->article);
    }

    public function rules(): array
    {
        return [
            'knowledge_category_id' => ['nullable', 'uuid', 'exists:knowledge_categories,uuid'],
            'title' => ['sometimes', 'array', 'size:2'],
            'title.ar' => ['required_with:title', 'string', 'max:255'],
            'title.en' => ['required_with:title', 'string', 'max:255'],
            'body' => ['sometimes', 'array', 'size:2'],
            'body.ar' => ['required_with:body', 'string'],
            'body.en' => ['required_with:body', 'string'],
            'visibility' => ['sometimes', Rule::enum(ArticleVisibility::class)],
        ];
    }
}
