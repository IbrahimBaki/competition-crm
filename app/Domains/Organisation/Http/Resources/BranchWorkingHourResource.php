<?php

namespace App\Domains\Organisation\Http\Resources;

use App\Domains\Organisation\Models\BranchWorkingHour;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin BranchWorkingHour */
class BranchWorkingHourResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'day_of_week' => (int) $this->day_of_week,
            'is_working' => (bool) $this->is_working,
            'opens_at' => $this->opens_at,
            'closes_at' => $this->closes_at,
        ];
    }
}
