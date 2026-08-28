<?php

namespace App\Domains\Customers\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'name' => $this->name,
            'preferred_locale' => $this->preferred_locale,
            'status' => $this->status->value,
            'blocked_reason' => $this->blocked_reason,
            'blocked_at' => $this->blocked_at?->toIso8601String(),
            'company_account' => new CustomerCompanyAccountResource($this->whenLoaded('companyAccount')),
            'contacts' => CustomerContactResource::collection($this->whenLoaded('contacts')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
