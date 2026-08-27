<?php

namespace App\Domains\Channels\Chat\Services\Availability;

use App\Domains\Channels\Chat\Models\ChatAvailabilityOutcome;
use App\Domains\Channels\Chat\Services\Queue\ChatQueue;
use App\Domains\Organisation\Models\Department;
use App\Domains\Organisation\Services\WorkingTimeService;

final class ChatAvailabilityResolver
{
    public function __construct(
        private readonly WorkingTimeService $workingTimeService,
        private readonly ChatQueue $queue,
    ) {}

    public function resolve(Department $department): ChatAvailability
    {
        $now = now();
        $branch = $department->branch;

        if ($branch && ! $this->isWithinWorkingHours($branch, $now)) {
            return new ChatAvailability(ChatAvailabilityOutcome::OfflineForm);
        }

        $agentCount = $this->countAvailableAgents($department);
        if ($agentCount > 0) {
            return new ChatAvailability(ChatAvailabilityOutcome::Connect);
        }

        $queueLength = $this->queue->length($department->id);
        $maxQueue = (int) config('channels.chat.max_queue_length', 20);
        if ($queueLength >= $maxQueue) {
            return new ChatAvailability(ChatAvailabilityOutcome::OfflineForm);
        }

        $behavior = config('channels.chat.no_agent_behaviour', 'both');
        $outcome = match ($behavior) {
            'queue' => ChatAvailabilityOutcome::Queue,
            'offline_form' => ChatAvailabilityOutcome::OfflineForm,
            default => ChatAvailabilityOutcome::QueueOrOfflineForm,
        };

        return new ChatAvailability($outcome);
    }

    private function isWithinWorkingHours($branch, $now): bool
    {
        $elapsed = $this->workingTimeService->elapsedWorkingMinutes($branch, $now, $now->copy()->addMinute());

        return $elapsed > 0;
    }

    private function countAvailableAgents(Department $department): int
    {
        $maxConcurrent = (int) config('channels.chat.max_concurrent_per_agent', 3);

        return $department->users()
            ->count();
    }
}
