<?php

namespace App\Domains\Knowledge\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RestoreArticleVersionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [];
    }
}
