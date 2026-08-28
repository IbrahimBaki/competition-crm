<?php

namespace App\Domains\Customers\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerDuplicateCandidateResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'customer' => [
                'uuid' => $this->customer->uuid,
                'name' => $this->customer->name,
            ],
            'duplicate_customer' => [
                'uuid' => $this->duplicateCustomer->uuid,
                'name' => $this->duplicateCustomer->name,
            ],
            'status' => $this->status->value,
            'rule' => $this->rule,
            'evidence' => $this->evidence,
            'reviewed_at' => $this->reviewed_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
