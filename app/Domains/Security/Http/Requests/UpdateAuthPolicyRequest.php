<?php

namespace App\Domains\Security\Http\Requests;

use App\Domains\Security\Permissions\PermissionKey;
use Illuminate\Foundation\Http\FormRequest;

class UpdateAuthPolicyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can(PermissionKey::ADMIN_USERS_MANAGE_TWO_FACTOR_POLICY);
    }

    public function rules(): array
    {
        return [
            'require_two_factor' => 'required|boolean',
        ];
    }
}
