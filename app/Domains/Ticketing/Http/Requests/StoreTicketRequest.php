<?php

namespace App\Domains\Ticketing\Http\Requests;

use App\Domains\Ticketing\Models\Ticket;
use Illuminate\Foundation\Http\FormRequest;

class StoreTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Ticket::class);
    }

    public function rules(): array
    {
        return [
            'customer_uuid' => ['required', 'string', 'exists:customers,uuid'],
            'department_uuid' => ['required', 'string', 'exists:departments,id'],
            'priority' => ['required', 'in:low,normal,high,urgent'],
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'category_uuid' => ['nullable', 'string', 'exists:ticket_categories,uuid'],
            'assignee_uuid' => ['nullable', 'string', 'exists:users,uuid'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string', 'max:64'],
            'custom_fields' => ['nullable', 'array'],
        ];
    }
}
