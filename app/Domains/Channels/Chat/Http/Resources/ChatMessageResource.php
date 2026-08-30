<?php

namespace App\Domains\Channels\Chat\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChatMessageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->uuid,
            'sequence' => $this->sequence,
            'author_type' => $this->author_type->value,
            'author_user_id' => $this->author?->uuid,
            'body' => $this->body,
            'sent_at' => $this->sent_at?->toIso8601String(),
            'client_message_id' => $this->client_message_id,
        ];
    }
}
