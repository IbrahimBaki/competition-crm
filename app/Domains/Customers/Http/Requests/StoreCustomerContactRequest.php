<?php

namespace App\Domains\Customers\Http\Requests;

use App\Domains\Customers\Models\ContactType;
use Illuminate\Foundation\Http\FormRequest;

class StoreCustomerContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $types = implode(',', array_map(fn ($case) => $case->value, ContactType::cases()));

        return [
            'type' => "required|in:{$types}",
            'value' => 'required|string|max:255',
            'label' => 'nullable|string|max:100',
            'is_primary' => 'boolean',
        ];
    }

    public function after(): array
    {
        return [
            function ($validator) {
                if ($this->type === 'email' && ! filter_var($this->value, FILTER_VALIDATE_EMAIL)) {
                    $validator->errors()->add('value', 'The value field must be a valid email address.');
                }
            },
        ];
    }
}
