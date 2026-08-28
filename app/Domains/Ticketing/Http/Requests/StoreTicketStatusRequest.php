<?php

namespace App\Domains\Ticketing\Http\Requests;

use App\Domains\Ticketing\Models\TicketStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTicketStatusRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'array'],
            'name.en' => ['required', 'string', 'max:255'],
            'name.ar' => ['required', 'string', 'max:255'],
            'lifecycle_type' => ['required', Rule::enum(TicketStatus::class)],
            'position' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
