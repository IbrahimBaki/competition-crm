<?php

namespace App\Domains\Workspace\Actions;

use App\Domains\Workspace\Models\AgentTask;
use App\Domains\Workspace\Models\AgentTaskEventType;
use App\Models\User;

readonly class ReassignAgentTask
{
    public function handle(AgentTask $task, User $newOwner, User $actor): AgentTask
    {
        $previousOwnerId = $task->owner_id;

        $task->update(['owner_id' => $newOwner->id]);

        $task->events()->create([
            'type' => AgentTaskEventType::Reassigned,
            'payload' => ['from_owner_id' => $previousOwnerId, 'to_owner_id' => $newOwner->id],
            'actor_id' => $actor->id,
        ]);

        return $task->refresh();
    }
}
