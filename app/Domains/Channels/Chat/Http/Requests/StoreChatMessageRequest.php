<?php

namespace App\Domains\Channels\Chat\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreChatMessageRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'body' => ['required', 'string', 'max:'.(int) config('channels.chat.max_message_length', 4000)],
            'client_message_id' => ['sometimes', 'nullable', 'string', 'max:64'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
