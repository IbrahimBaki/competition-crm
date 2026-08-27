<?php

namespace App\Domains\Knowledge\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreArticleFeedbackRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'is_helpful' => ['required', 'boolean'],
            'visitor_key' => ['nullable', 'string', 'max:255'],
        ];
    }
}
