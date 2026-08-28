<?php

namespace App\Domains\Ticketing\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('ticket'));
    }

    public function rules(): array
    {
        return [
            'subject' => ['sometimes', 'string', 'max:255'],
            'body' => ['sometimes', 'string'],
            'priority' => ['sometimes', 'in:low,normal,high,urgent'],
            'status' => ['sometimes', 'in:new,open,pending,resolved,closed'],
        ];
    }
}
