<?php

namespace App\Domains\Integrations\Services\Retention;

use App\Domains\Integrations\Models\ImportRun;
use App\Support\Retention\PurgeHandler;
use App\Support\Retention\RetentionPolicy;

final class ImportRunPurgeHandler implements PurgeHandler
{
    public function dataClass(): string
    {
        return 'import_runs';
    }

    public function purge(RetentionPolicy $policy): int
    {
        if ($policy->days === null) {
            return 0;
        }

        return ImportRun::where('created_at', '<', $policy->cutoff)
            ->where(function ($q) {
                $q->where('state', 'completed')
                    ->orWhere('state', 'failed');
            })
            ->delete();
    }
}
