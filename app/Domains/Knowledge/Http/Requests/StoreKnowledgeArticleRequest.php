<?php

namespace App\Domains\Knowledge\Http\Requests;

use App\Domains\Knowledge\Models\ArticleVisibility;
use App\Domains\Knowledge\Models\KnowledgeArticle;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreKnowledgeArticleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', KnowledgeArticle::class);
    }

    public function rules(): array
    {
        return [
            'knowledge_category_id' => ['nullable', 'uuid', 'exists:knowledge_categories,uuid'],
            'title' => ['required', 'array', 'size:2'],
            'title.ar' => ['required', 'string', 'max:255'],
            'title.en' => ['required', 'string', 'max:255'],
            'body' => ['required', 'array', 'size:2'],
            'body.ar' => ['required', 'string'],
            'body.en' => ['required', 'string'],
            'visibility' => ['required', Rule::enum(ArticleVisibility::class)],
        ];
    }
}
