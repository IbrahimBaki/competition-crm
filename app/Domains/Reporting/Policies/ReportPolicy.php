<?php

declare(strict_types=1);

namespace App\Domains\Reporting\Policies;

use App\Models\User;

class ReportPolicy
{
    public function viewAny(User $user, string $reportKey): bool
    {
        return $user->can('reports.view.any')
            || $user->can('reports.view.department')
            || $user->can('reports.view.own');
    }
}
