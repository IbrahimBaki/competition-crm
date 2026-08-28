<?php

namespace App\Domains\Security\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'display_name' => ['required', 'array'],
            'display_name.ar' => ['required', 'string', 'max:255'],
            'display_name.en' => ['required', 'string', 'max:255'],
            'permission_keys' => ['required', 'array'],
            'permission_keys.*' => ['string'],
        ];
    }
}
