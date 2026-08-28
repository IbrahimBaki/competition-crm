<?php

namespace App\Domains\Security\Http\Requests;

use App\Domains\Security\Permissions\PermissionKey;
use Illuminate\Foundation\Http\FormRequest;

class ActivateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can(PermissionKey::ADMIN_USERS_ACTIVATE);
    }

    public function rules(): array
    {
        return [];
    }
}
