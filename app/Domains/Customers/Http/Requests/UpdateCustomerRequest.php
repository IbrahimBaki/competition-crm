<?php

namespace App\Domains\Customers\Http\Requests;

use App\Support\I18n\LocaleResolver;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:255',
            'company_account_id' => 'sometimes|nullable|exists:company_accounts,id',
            'preferred_locale' => 'sometimes|in:'.implode(',', LocaleResolver::SUPPORTED),
        ];
    }
}
