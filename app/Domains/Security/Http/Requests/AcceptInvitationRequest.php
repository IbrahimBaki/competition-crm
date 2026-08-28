<?php

namespace App\Domains\Security\Http\Requests;

use App\Domains\Security\Rules\StaffPasswordRules;
use Illuminate\Foundation\Http\FormRequest;

class AcceptInvitationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:120',
            'password' => StaffPasswordRules::default(),
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'token' => $this->route('token'),
        ]);
    }
}
