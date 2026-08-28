<?php

namespace App\Domains\Knowledge\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class KnowledgeCategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->uuid,
            'code' => $this->code,
            'name' => $this->name,
            'depth' => $this->depth,
            'position' => $this->position,
            'is_active' => $this->is_active,
            'parent_id' => $this->parent_id ? $this->parent->uuid : null,
        ];
    }
}
