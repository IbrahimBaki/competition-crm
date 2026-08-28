<?php

namespace App\Domains\Portal\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PortalAccountResource extends JsonResource
{
    public const ALLOWED_FIELDS = ['uuid', 'email', 'locale', 'email_verified_at'];

    public function toArray($request): array
    {
        return [
            'uuid' => $this->uuid,
            'email' => $this->email,
            'locale' => $this->locale,
            'email_verified_at' => $this->email_verified_at,
        ];
    }
}
