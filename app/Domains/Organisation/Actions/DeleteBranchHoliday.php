<?php

namespace App\Domains\Organisation\Actions;

use App\Domains\Organisation\Models\BranchHoliday;
use App\Domains\Security\Services\AuditLogger;
use App\Models\User;

class DeleteBranchHoliday
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function execute(BranchHoliday $holiday, ?User $actor = null): void
    {
        \DB::transaction(function () use ($holiday, $actor) {
            $before = $holiday->toArray();

            $holiday->delete();

            $this->auditLogger->record(
                $actor,
                'organisation.branch.holiday.deleted',
                $holiday,
                $before,
                null
            );
        });
    }
}
