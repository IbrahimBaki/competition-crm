<?php

namespace App\Domains\Organisation\Http\Resources;

use App\Domains\Organisation\Models\BranchHoliday;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin BranchHoliday */
class BranchHolidayResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => (string) $this->id,
            'name' => $this->name,
            'date' => $this->date,
            'recurring_month_day' => $this->recurring_month_day,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
