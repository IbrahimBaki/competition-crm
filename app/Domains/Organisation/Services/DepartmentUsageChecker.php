<?php

namespace App\Domains\Organisation\Services;

interface DepartmentUsageChecker
{
    public function openTicketCount(string $departmentId): int;
}
