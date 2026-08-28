<?php

namespace App\Domains\Workspace\Actions;

use App\Domains\Organisation\Models\Branch;
use App\Domains\Organisation\Services\WorkingTimeService;
use App\Domains\Workspace\Models\AgentTask;
use App\Domains\Workspace\Models\AgentTaskEventType;
use App\Models\User;
use DateTimeInterface;

readonly class ScheduleAgentTaskReminder
{
    public function __construct(private readonly WorkingTimeService $workingTimeService) {}

    public function handle(AgentTask $task, DateTimeInterface $reminderAt, User $actor): AgentTask
    {
        $resolvedReminderAt = $reminderAt;

        if ($task->due_in_working_time && $task->branch instanceof Branch) {
            $resolvedReminderAt = $this->workingTimeService->addWorkingMinutes(
                $task->branch,
                $reminderAt,
                0,
            );
        }

        $task->update([
            'reminder_at' => $resolvedReminderAt,
            'reminder_sent_at' => null,
        ]);

        $task->events()->create([
            'type' => AgentTaskEventType::ReminderScheduled,
            'payload' => ['reminder_at' => $resolvedReminderAt->format('c')],
            'actor_id' => $actor->id,
        ]);

        return $task->refresh();
    }
}
