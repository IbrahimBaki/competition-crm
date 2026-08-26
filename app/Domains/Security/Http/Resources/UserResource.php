<?php

namespace App\Domains\Security\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'email' => $this->email,
            'name' => $this->name,
            'is_active' => $this->isActive(),
            'has_two_factor' => $this->hasTwoFactorEnabled(),
            'last_login_at' => $this->last_login_at,
            'deactivated_at' => $this->deactivated_at,
        ];
    }
}
