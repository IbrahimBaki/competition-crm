<?php

namespace App\Domains\Notifications\Events;

use App\Domains\Notifications\Enums\NotificationEventType;
use Illuminate\Contracts\Queue\ShouldQueue;

final readonly class AgentTaskReminderNotification implements NotifiableEvent, ShouldQueue
{
    public function __construct(
        private string $id,
        private int $taskId,
        private string $taskTitle,
        private string $taskDueAt,
        private int $recipientId,
        private ?string $ticketReference = null,
    ) {}

    public function eventId(): string
    {
        return $this->id;
    }

    public function eventType(): NotificationEventType
    {
        return NotificationEventType::AgentTaskReminder;
    }

    public function recipientIds(): array
    {
        return [$this->recipientId];
    }

    public function payload(): array
    {
        return [
            'task_id' => $this->taskId,
            'task_title' => $this->taskTitle,
            'task_due_at' => $this->taskDueAt,
            'ticket_reference' => $this->ticketReference,
        ];
    }
}
