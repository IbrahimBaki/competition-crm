<?php

namespace App\Domains\Ticketing\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SplitTicketRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'category' => ['nullable', 'uuid', Rule::exists('ticket_categories', 'uuid')],
        ];
    }
}
