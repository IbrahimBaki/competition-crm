<?php

declare(strict_types=1);

namespace App\Domains\Reporting\Http\Resources;

use App\Domains\Reporting\Services\Definitions\ReportDefinition;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin ReportDefinition */
class ReportDefinitionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var ReportDefinition $this */
        return [
            'key' => $this->key(),
            'permission' => $this->permission(),
            'columns' => $this->columns(),
        ];
    }
}
