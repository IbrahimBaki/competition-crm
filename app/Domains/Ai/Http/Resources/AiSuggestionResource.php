<?php

namespace App\Domains\Ai\Http\Resources;

use App\Domains\Ai\Models\AiSuggestion;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin AiSuggestion */
class AiSuggestionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->uuid,
            'ticket_id' => $this->ticket?->uuid,
            'feature' => $this->feature?->value,
            'state' => $this->state?->value,
            'content' => $this->content,
            'confidence' => $this->confidence !== null ? (float) $this->confidence : null,
            'model' => $this->model,
            'requested_by_user_id' => $this->requestedBy?->uuid,
            'resolved_by_user_id' => $this->resolvedBy?->uuid,
            'resolved_at' => $this->resolved_at?->toIso8601String(),
            'metadata' => $this->metadata,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
