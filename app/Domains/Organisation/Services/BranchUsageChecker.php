<?php

namespace App\Domains\Organisation\Services;

interface BranchUsageChecker
{
    public function activeDepartmentCount(string $branchId): int;
}
