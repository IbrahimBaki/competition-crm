<?php

namespace App\Domains\Integrations\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class IssuedApiTokenResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->uuid,
            'name' => $this->name,
            'token' => $this->plaintext,
            'token_prefix' => $this->token_prefix,
            'scopes' => $this->scopes,
            'created_at' => now()->toIso8601String(),
        ];
    }
}
