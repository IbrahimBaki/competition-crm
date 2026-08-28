<?php

declare(strict_types=1);

namespace App\Domains\Reporting\Http\Resources;

use App\Domains\Reporting\Services\Definitions\ReportResult;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin ReportResult */
class ReportResultResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var ReportResult $this */
        return [
            'generated_at' => $this->generatedAt->toIso8601String(),
            'timezone' => $this->timezone,
            'rows' => $this->rows,
            'totals' => $this->totals,
        ];
    }
}
