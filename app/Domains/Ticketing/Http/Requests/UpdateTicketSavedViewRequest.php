<?php

namespace App\Domains\Ticketing\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTicketSavedViewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:120'],
            'query' => ['sometimes', 'array'],
            'is_shared' => ['sometimes', 'boolean'],
        ];
    }
}
