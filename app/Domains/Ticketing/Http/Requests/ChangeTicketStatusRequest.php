<?php

namespace App\Domains\Ticketing\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ChangeTicketStatusRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'status' => ['required', 'uuid', Rule::exists('ticket_statuses', 'uuid')],
            'reason' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
