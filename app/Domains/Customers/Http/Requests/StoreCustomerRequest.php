<?php

namespace App\Domains\Customers\Http\Requests;

use App\Support\I18n\LocaleResolver;
use Illuminate\Foundation\Http\FormRequest;

class StoreCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'company_account_id' => 'nullable|exists:company_accounts,id',
            'preferred_locale' => 'nullable|in:'.implode(',', LocaleResolver::SUPPORTED),
        ];
    }
}
