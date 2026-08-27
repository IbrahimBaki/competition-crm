<?php

namespace App\Domains\Ticketing\Http\Requests;

use App\Domains\Ticketing\Models\MessageChannel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTicketMessageRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'body' => ['required', 'string', 'max:20000'],
            'body_format' => ['sometimes', 'string', 'in:text,html'],
            'channel' => ['required', 'string', Rule::enum(MessageChannel::class)],
            'is_internal' => ['sometimes', 'boolean'],
            'attachment_uuids' => ['sometimes', 'array', 'max:10'],
            'attachment_uuids.*' => ['uuid'],
            'template_key' => ['nullable', 'string', 'max:100'],
            'template_variables' => ['nullable', 'array'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
