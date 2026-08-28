<?php

namespace App\Domains\Workspace\Actions;

use App\Domains\Workspace\Exceptions\IllegalAgentTaskTransitionException;
use App\Domains\Workspace\Models\AgentTask;
use App\Domains\Workspace\Models\AgentTaskEventType;
use App\Domains\Workspace\Models\AgentTaskState;
use App\Models\User;

readonly class ChangeAgentTaskState
{
    private const TRANSITIONS = [
        AgentTaskState::Open->value => [AgentTaskState::InProgress->value, AgentTaskState::Done->value, AgentTaskState::Cancelled->value],
        AgentTaskState::InProgress->value => [AgentTaskState::Open->value, AgentTaskState::Done->value, AgentTaskState::Cancelled->value],
        AgentTaskState::Done->value => [],
        AgentTaskState::Cancelled->value => [],
    ];

    public function handle(AgentTask $task, AgentTaskState $newState, User $actor): AgentTask
    {
        $allowedTransitions = self::TRANSITIONS[$task->state->value] ?? [];

        if (! in_array($newState->value, $allowedTransitions)) {
            throw new IllegalAgentTaskTransitionException;
        }

        $task->update(['state' => $newState]);

        if ($newState === AgentTaskState::Done) {
            $task->update(['completed_at' => now()]);
        } elseif ($newState === AgentTaskState::Cancelled) {
            $task->update(['cancelled_at' => now()]);
        }

        $task->events()->create([
            'type' => AgentTaskEventType::StateChanged,
            'payload' => ['from' => $task->getOriginal('state'), 'to' => $newState->value],
            'actor_id' => $actor->id,
        ]);

        return $task->refresh();
    }
}
