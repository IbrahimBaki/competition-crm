<?php

namespace App\Domains\Portal\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PortalTicketMessageResource extends JsonResource
{
    public const ALLOWED_FIELDS = ['uuid', 'body', 'author_type', 'created_at', 'attachments'];

    public function __construct($resource)
    {
        parent::__construct($resource);

        if ($this->resource && $this->resource->is_internal) {
            throw new \RuntimeException('Cannot serialize internal message to portal');
        }
    }

    public function toArray($request): array
    {
        return [
            'uuid' => $this->uuid,
            'body' => $this->body,
            'author_type' => $this->author_type === 'system' ? 'agent' : 'customer',
            'created_at' => $this->created_at,
            'attachments' => PortalAttachmentResource::collection($this->attachments ?? []),
        ];
    }
}
