<?php

namespace App\Console\Commands;

use App\Domains\Notifications\Events\AgentTaskReminderNotification;
use App\Domains\Notifications\Services\NotificationDispatcher;
use App\Domains\Workspace\Models\AgentTask;
use App\Domains\Workspace\Models\AgentTaskEventType;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class AgentTaskReminderSweepCommand extends Command
{
    protected $signature = 'agent-tasks:reminder-sweep';

    protected $description = 'Dispatch reminders for due agent tasks';

    public function __construct(
        private NotificationDispatcher $dispatcher,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $tasks = AgentTask::query()
            ->where('reminder_at', '<=', now())
            ->whereNull('reminder_sent_at')
            ->whereIn('state', ['open', 'in_progress'])
            ->limit(100)
            ->get();

        foreach ($tasks as $task) {
            try {
                $this->dispatcher->handle(
                    new AgentTaskReminderNotification(
                        Str::uuid()->toString(),
                        $task->id,
                        $task->title,
                        $task->due_at?->toIso8601String() ?? '',
                        $task->owner_id,
                        $task->ticket?->reference,
                    )
                );
                $task->update(['reminder_sent_at' => now()]);
                $task->events()->create(['type' => AgentTaskEventType::ReminderFired]);
            } catch (\Throwable) {
                continue;
            }
        }

        return 0;
    }
}
