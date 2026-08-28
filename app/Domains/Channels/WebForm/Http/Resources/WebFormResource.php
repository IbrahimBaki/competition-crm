<?php

namespace App\Domains\Channels\WebForm\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WebFormResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->uuid,
            'key' => $this->key,
            'title' => $this->title,
            'description' => $this->description,
            'department_id' => $this->department_id,
            'ticket_category_id' => $this->ticket_category_id,
            'default_priority' => $this->default_priority,
            'acknowledgement_template_key' => $this->acknowledgement_template_key,
            'is_active' => $this->is_active,
            'fields' => WebFormFieldResourceAdmin::collection($this->fields),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

class WebFormFieldResourceAdmin extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->uuid,
            'key' => $this->key,
            'type' => $this->type->value,
            'is_required' => $this->is_required,
            'label' => $this->label,
            'options' => $this->options,
            'validation' => $this->validation,
            'maps_to' => $this->maps_to,
            'position' => $this->position,
        ];
    }
}
