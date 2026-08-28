<?php

namespace App\Domains\Customers\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MergeCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'duplicate_customer_uuid' => ['required', 'uuid', 'exists:customers,uuid'],
        ];
    }
}
