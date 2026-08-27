<?php

declare(strict_types=1);

namespace App\Domains\Reporting\Policies;

use App\Models\User;

class ReportPolicy
{
    public function viewAny(User $user, string $reportKey): bool
    {
        $permissionKey = $this->getPermissionKeyForReport($reportKey);

        return $user->can($permissionKey);
    }

    private function getPermissionKeyForReport(string $reportKey): string
    {
        // Map report keys to permission keys - all use reports.view scope
        return 'reports.view.any';
    }
}
