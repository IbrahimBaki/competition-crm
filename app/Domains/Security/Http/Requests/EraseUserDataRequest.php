<?php

namespace App\Domains\Security\Http\Requests;

use App\Domains\Security\Permissions\PermissionKey;
use Illuminate\Foundation\Http\FormRequest;

class EraseUserDataRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can(PermissionKey::DATAPROTECTION_ERASURE_EXECUTE);
    }

    public function rules(): array
    {
        return [
            'reason' => 'required|string|max:500',
        ];
    }
}
