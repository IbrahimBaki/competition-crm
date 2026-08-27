<?php

namespace App\Domains\Channels\WebForm\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubmitWebFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'answers' => ['required', 'array'],
        ];
    }
}
