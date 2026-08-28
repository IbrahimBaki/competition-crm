<?php

namespace App\Domains\Organisation\Services;

use App\Domains\Organisation\Models\Department;

class DefaultBranchUsageChecker implements BranchUsageChecker
{
    public function activeDepartmentCount(string $branchId): int
    {
        return Department::where('branch_id', $branchId)
            ->where('is_active', true)
            ->count();
    }
}
