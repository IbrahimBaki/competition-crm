<?php

namespace App\Domains\Automation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EscalateTicketRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->user()->can('tickets.escalate');
    }

    public function rules()
    {
        return [
            'level' => 'required|integer|min:1',
            'reason' => 'required|string|min:3|max:2000',
        ];
    }
}
