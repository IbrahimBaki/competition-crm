<?php

namespace App\Domains\Ticketing\Http\Requests;

use App\Domains\Ticketing\Models\TicketStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTicketStatusRequest extends FormRequest
{
    public function rules(): array
    {
        $status = $this->route('status');

        return [
            'name' => ['nullable', 'array'],
            'name.en' => ['required_with:name', 'string', 'max:255'],
            'name.ar' => ['required_with:name', 'string', 'max:255'],
            'lifecycle_type' => ['nullable', $status->is_system ? 'prohibited' : Rule::enum(TicketStatus::class)],
            'position' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'is_default' => ['nullable', 'boolean'],
        ];
    }
}
