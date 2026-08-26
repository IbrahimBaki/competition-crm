<?php

namespace App\Domains\Organisation\Actions;

use App\Domains\Organisation\Models\Branch;
use App\Domains\Organisation\Models\BranchHoliday;
use App\Domains\Security\Services\AuditLogger;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Str;

class CreateBranchHoliday
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function execute(Branch $branch, array $data, ?Authenticatable $actor = null): BranchHoliday
    {
        return \DB::transaction(function () use ($branch, $data, $actor) {
            $holiday = BranchHoliday::create([
                'id' => Str::uuid(),
                'branch_id' => $branch->id,
                'name' => $data['name'],
                'date' => $data['date'] ?? null,
                'recurring_month_day' => $data['recurring_month_day'] ?? null,
            ]);

            $this->auditLogger->record(
                $actor,
                'organisation.branch.holiday.created',
                $holiday,
                null,
                $holiday->toArray()
            );

            return $holiday;
        });
    }
}
