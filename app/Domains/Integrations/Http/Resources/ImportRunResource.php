<?php

namespace App\Domains\Integrations\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class ImportRunResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->uuid,
            'kind' => $this->kind,
            'mode' => $this->mode,
            'state' => $this->state,
            'source_attachment_id' => $this->sourceAttachment?->uuid,
            'total_rows' => (int) $this->total_rows,
            'valid_rows' => (int) $this->valid_rows,
            'imported_rows' => (int) $this->imported_rows,
            'failed_rows' => (int) $this->failed_rows,
            'error_report' => $this->error_report,
            'started_at' => $this->started_at?->toIso8601String(),
            'finished_at' => $this->finished_at?->toIso8601String(),
            'created_by_user_id' => $this->createdBy?->uuid,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
