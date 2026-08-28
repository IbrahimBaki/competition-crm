<?php

namespace App\Domains\Ticketing\Http\Requests;

use App\Domains\Ticketing\Models\TicketLinkRelation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTicketLinkRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'target' => ['required', 'uuid', Rule::exists('tickets', 'uuid')],
            'relation' => ['required', Rule::enum(TicketLinkRelation::class)],
        ];
    }
}
