<?php

namespace App\Domains\Workspace\Actions;

use App\Domains\Workspace\Exceptions\AgentTaskOwnerUnavailableException;
use App\Domains\Workspace\Models\AgentTask;
use App\Domains\Workspace\Models\AgentTaskEventType;
use App\Models\User;
use DateTimeInterface;

readonly class CreateAgentTask
{
    public function handle(
        User $owner,
        User $creator,
        string $title,
        ?string $description = null,
        ?DateTimeInterface $dueAt = null,
        bool $dueInWorkingTime = false,
        ?int $ticketId = null,
        ?int $branchId = null,
    ): AgentTask {
        if (! $owner->is_active) {
            throw new AgentTaskOwnerUnavailableException;
        }

        $task = AgentTask::create([
            'owner_id' => $owner->id,
            'created_by_id' => $creator->id,
            'ticket_id' => $ticketId,
            'title' => $title,
            'description' => $description,
            'due_at' => $dueAt,
            'due_in_working_time' => $dueInWorkingTime,
            'branch_id' => $branchId,
            'state' => 'open',
        ]);

        $task->events()->create([
            'type' => AgentTaskEventType::Created,
            'actor_id' => $creator->id,
        ]);

        return $task->refresh();
    }
}
