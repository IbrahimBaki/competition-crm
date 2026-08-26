<?php

namespace App\Domains\Security\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvitationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->id,  // The primary key is a UUID
            'email' => $this->email,
            'expires_at' => $this->expires_at,
            'accepted_at' => $this->accepted_at,
        ];
    }
}
