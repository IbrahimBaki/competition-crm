<?php

namespace App\Domains\Portal\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PortalAttachmentResource extends JsonResource
{
    public const ALLOWED_FIELDS = ['uuid', 'original_name', 'size', 'mime_type', 'download_url'];

    public function toArray($request): array
    {
        return [
            'uuid' => $this->uuid,
            'original_name' => $this->original_name,
            'size' => $this->size_bytes,
            'mime_type' => $this->mime_type,
            'download_url' => '/api/v1/portal/attachments/'.$this->uuid,
        ];
    }
}
