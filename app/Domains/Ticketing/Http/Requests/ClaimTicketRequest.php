<?php

namespace App\Domains\Ticketing\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ClaimTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('claim', $this->route('ticket'));
    }

    public function rules(): array
    {
        return [
            'version' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
