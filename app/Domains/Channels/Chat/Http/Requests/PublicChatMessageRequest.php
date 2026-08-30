<?php

namespace App\Domains\Channels\Chat\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PublicChatMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'visitor_token' => ['required', 'string'],
            'body' => ['required', 'string', 'max:'.(int) config('channels.chat.max_message_length', 4000)],
            'client_message_id' => ['sometimes', 'nullable', 'string', 'max:64'],
        ];
    }
}
