<?php

namespace App\Domains\Customers\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerNoteResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'body' => $this->body,
            'author_uuid' => $this->author?->uuid,
            'author_name' => $this->author?->name,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
