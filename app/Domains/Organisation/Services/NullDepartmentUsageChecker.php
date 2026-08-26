<?php

namespace App\Domains\Organisation\Services;

class NullDepartmentUsageChecker implements DepartmentUsageChecker
{
    public function openTicketCount(string $departmentId): int
    {
        return 0;
    }
}
