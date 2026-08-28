<?php

namespace App\Support\Attachments\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property string $uuid
 * @property string $original_name
 * @property string $mime_type
 * @property int $size_bytes
 * @property string $scan_state
 * @property string $created_at
 */
class AttachmentResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'original_name' => $this->original_name,
            'mime_type' => $this->mime_type,
            'size_bytes' => $this->size_bytes,
            'scan_state' => $this->scan_state,
            'created_at' => $this->created_at,
        ];
    }
}
