<?php

declare(strict_types=1);

namespace App\Domains\Reporting\Services\Retention;

use App\Domains\Reporting\Models\ReportExport;
use App\Support\Retention\PurgeHandler;
use App\Support\Retention\RetentionPolicy;

class ReportExportPurgeHandler implements PurgeHandler
{
    public function dataClass(): string
    {
        return 'reports';
    }

    public function purge(RetentionPolicy $policy): int
    {
        if ($policy->days === null) {
            return 0;
        }

        return ReportExport::where('expires_at', '<', $policy->cutoff)
            ->delete();
    }
}
