<?php

namespace App\Domains\Ticketing\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MarkTicketSpamRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'reason' => ['required', 'string', 'max:1000'],
        ];
    }
}
