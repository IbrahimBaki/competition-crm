<?php

namespace App\Domains\Channels\WebForm\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PublicWebFormResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->uuid,
            'key' => $this->key,
            'title' => $this->title,
            'description' => $this->description,
            'fields' => PublicWebFormFieldResource::collection($this->fields),
        ];
    }
}

class PublicWebFormFieldResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->uuid,
            'key' => $this->key,
            'label' => $this->label,
            'type' => $this->type->value,
            'is_required' => $this->is_required,
            'options' => $this->options,
        ];
    }
}
