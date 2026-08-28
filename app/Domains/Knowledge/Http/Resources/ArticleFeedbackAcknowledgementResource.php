<?php

namespace App\Domains\Knowledge\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArticleFeedbackAcknowledgementResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->uuid,
            'is_helpful' => $this->is_helpful,
            'created_at' => $this->created_at,
        ];
    }
}
