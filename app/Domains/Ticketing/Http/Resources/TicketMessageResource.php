<?php

namespace App\Domains\Ticketing\Http\Resources;

use App\Support\Attachments\Resources\AttachmentResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketMessageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'direction' => $this->direction->value,
            'author_type' => $this->author_type->value,
            'author' => $this->author ? [
                'uuid' => $this->author->uuid,
                'display_name' => $this->author->name,
            ] : null,
            'channel' => $this->channel->value,
            'is_internal' => $this->is_internal,
            'body' => $this->body,
            'body_format' => $this->body_format,
            'delivery_state' => $this->delivery_state?->value,
            'failure_reason' => $this->failure_reason,
            'retry_count' => $this->retry_count,
            'queued_at' => $this->queued_at?->toIso8601String(),
            'sent_at' => $this->sent_at?->toIso8601String(),
            'delivered_at' => $this->delivered_at?->toIso8601String(),
            'read_at' => $this->read_at?->toIso8601String(),
            'failed_at' => $this->failed_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'attachments' => AttachmentResource::collection($this->attachments),
        ];
    }
}
