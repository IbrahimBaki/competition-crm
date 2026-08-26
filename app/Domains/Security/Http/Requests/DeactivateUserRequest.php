<?php

namespace App\Domains\Security\Http\Requests;

use App\Domains\Security\Permissions\PermissionKey;
use Illuminate\Foundation\Http\FormRequest;

class DeactivateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can(PermissionKey::ADMIN_USERS_DEACTIVATE);
    }

    public function rules(): array
    {
        return [];
    }
}
