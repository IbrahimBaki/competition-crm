<?php

declare(strict_types=1);

namespace App\Domains\Reporting\Http\Resources;

use App\Domains\Reporting\Models\ReportSchedule;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin ReportSchedule */
class ReportScheduleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->uuid,
            'name' => $this->name,
            'report_key' => $this->report_key,
            'format' => $this->format,
            'filters' => $this->filters,
            'frequency' => $this->frequency,
            'day_of_week' => $this->day_of_week !== null ? (int) $this->day_of_week : null,
            'day_of_month' => $this->day_of_month !== null ? (int) $this->day_of_month : null,
            'run_at_time' => $this->run_at_time,
            'timezone' => $this->timezone,
            'recipients' => $this->recipients,
            'is_active' => (bool) $this->is_active,
            'created_by_user_id' => $this->createdBy?->uuid,
            'last_run_at' => $this->last_run_at?->toIso8601String(),
            'next_run_at' => $this->next_run_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
