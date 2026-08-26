<?php

namespace App\Domains\Workspace\Services\Retention;

use App\Domains\Workspace\Models\AgentTask;
use App\Support\Retention\PurgeHandler;
use App\Support\Retention\RetentionPolicy;

final class AgentTaskPurgeHandler implements PurgeHandler
{
    public function dataClass(): string
    {
        return 'agent_tasks';
    }

    public function purge(RetentionPolicy $policy): int
    {
        if ($policy->days === null) {
            return 0;
        }

        $ids = AgentTask::query()
            ->whereIn('state', ['done', 'cancelled'])
            ->where('updated_at', '<', $policy->cutoff)
            ->pluck('id')
            ->toArray();

        if (empty($ids)) {
            return 0;
        }

        return AgentTask::destroy($ids);
    }
}
