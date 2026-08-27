<?php

namespace App\Domains\Workspace\Actions;

use App\Domains\Workspace\Models\AgentTask;
use App\Domains\Workspace\Models\AgentTaskEvent;
use App\Models\User;

class UpdateAgentTask
{
    public function handle(AgentTask $task, array $data, ?User $actor = null): AgentTask
    {
        $task->update($data);

        $task->events()->create([
            'type' => AgentTaskEvent::Updated->value,
            'user_id' => $actor?->id,
            'metadata' => $data,
        ]);

        return $task->fresh();
    }
}
