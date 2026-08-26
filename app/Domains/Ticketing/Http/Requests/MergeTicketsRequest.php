<?php

namespace App\Domains\Ticketing\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MergeTicketsRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'target' => ['required', 'uuid', Rule::exists('tickets', 'uuid')],
        ];
    }
}
