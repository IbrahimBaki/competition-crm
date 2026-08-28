<?php

namespace App\Domains\Channels\Chat\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PublicEndChatSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'visitor_token' => ['required', 'string'],
            'reason' => ['sometimes', 'nullable', 'string', 'max:500'],
        ];
    }
}
