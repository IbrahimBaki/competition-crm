<?php

namespace App\Domains\Knowledge\Http\Requests;

use App\Domains\Knowledge\Models\ArticleVisibility;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ChangeArticleVisibilityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'visibility' => ['required', Rule::enum(ArticleVisibility::class)],
        ];
    }
}
