<?php

namespace App\Domains\Organisation\Actions;

use App\Domains\Organisation\Models\BranchHoliday;
use App\Domains\Security\Services\AuditLogger;
use App\Models\User;

class UpdateBranchHoliday
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function execute(BranchHoliday $holiday, array $data, ?User $actor = null): BranchHoliday
    {
        return \DB::transaction(function () use ($holiday, $data, $actor) {
            $before = $holiday->toArray();

            $holiday->update($data);

            $after = $holiday->toArray();

            $this->auditLogger->record(
                $actor,
                'organisation.branch.holiday.updated',
                $holiday,
                $before,
                $after
            );

            return $holiday;
        });
    }
}
