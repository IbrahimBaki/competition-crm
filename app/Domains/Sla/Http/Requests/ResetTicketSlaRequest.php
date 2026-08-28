<?php

namespace App\Domains\Sla\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ResetTicketSlaRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'reason' => ['required', 'string', 'max:500'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
