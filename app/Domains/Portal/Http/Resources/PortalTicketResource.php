<?php

namespace App\Domains\Portal\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PortalTicketResource extends JsonResource
{
    public const ALLOWED_FIELDS = [
        'uuid', 'reference', 'subject', 'status', 'priority',
        'created_at', 'updated_at', 'resolved_at',
    ];

    public function toArray($request): array
    {
        $data = [];

        foreach (self::ALLOWED_FIELDS as $field) {
            if ($this->offsetExists($field)) {
                $data[$field] = $this->offsetGet($field);
            }
        }

        return $data;
    }
}
