<?php

namespace App\Domains\Portal\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PortalTicketFeedbackResource extends JsonResource
{
    public const ALLOWED_FIELDS = ['uuid', 'score', 'comment', 'submitted_at'];

    public function toArray($request): array
    {
        return [
            'uuid' => $this->uuid,
            'score' => $this->score,
            'comment' => $this->comment,
            'submitted_at' => $this->submitted_at,
        ];
    }
}
