<?php

namespace App\Domains\Knowledge\Http\Requests;

use App\Domains\Knowledge\Models\ArticleState;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ChangeArticleStateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'state' => ['required', Rule::enum(ArticleState::class)],
        ];
    }
}
