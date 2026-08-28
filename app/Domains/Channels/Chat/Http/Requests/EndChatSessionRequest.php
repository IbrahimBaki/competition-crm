<?php

namespace App\Domains\Channels\Chat\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EndChatSessionRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'reason' => ['sometimes', 'nullable', 'string', 'max:500'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
