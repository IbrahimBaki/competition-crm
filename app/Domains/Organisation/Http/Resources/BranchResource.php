<?php

namespace App\Domains\Organisation\Http\Resources;

use App\Domains\Organisation\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\App;

/** @mixin Branch */
class BranchResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => (string) $this->id,
            'name' => $this->name->forLocale(App::getLocale()),
            'code' => $this->code,
            'timezone' => $this->timezone,
            'is_24_7' => (bool) $this->is_24_7,
            'is_active' => (bool) $this->is_active,
            'working_hours' => BranchWorkingHourResource::collection($this->whenLoaded('workingHours')),
            'holidays' => BranchHolidayResource::collection($this->whenLoaded('holidays')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
