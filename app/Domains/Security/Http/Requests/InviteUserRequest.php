<?php

namespace App\Domains\Security\Http\Requests;

use App\Domains\Security\Permissions\PermissionKey;
use Illuminate\Foundation\Http\FormRequest;

class InviteUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can(PermissionKey::ADMIN_USERS_INVITE);
    }

    public function rules(): array
    {
        return [
            'email' => 'required|email:rfc,dns|unique:users,email',
        ];
    }
}
