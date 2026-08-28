<?php

namespace App\Domains\Ticketing\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TransferTicketToAgentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('transferToAgent', $this->route('ticket'));
    }

    public function rules(): array
    {
        return [
            'user_uuid' => ['required', 'uuid', 'exists:users,uuid'],
            'version' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
