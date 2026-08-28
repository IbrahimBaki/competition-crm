<?php

namespace App\Domains\Security\Http\Requests;

use App\Domains\Security\Rules\StaffPasswordRules;
use Illuminate\Foundation\Http\FormRequest;

class ResetPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'token' => 'required|string',
            'email' => 'required|email',
            'password' => StaffPasswordRules::default(),
        ];
    }
}
