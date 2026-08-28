<?php

namespace App\Domains\Customers\Http\Requests;

use App\Domains\Customers\Models\ContactType;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCustomerContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $types = implode(',', array_map(fn ($case) => $case->value, ContactType::cases()));

        return [
            'value' => 'sometimes|string|max:255',
            'label' => 'sometimes|nullable|string|max:100',
            'is_primary' => 'sometimes|boolean',
        ];
    }
}
