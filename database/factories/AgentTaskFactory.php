<?php

namespace Database\Factories;

use App\Domains\Ticketing\Models\Ticket;
use App\Domains\Workspace\Models\AgentTask;
use App\Domains\Workspace\Models\AgentTaskState;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class AgentTaskFactory extends Factory
{
    protected $model = AgentTask::class;

    public function definition(): array
    {
        return [
            'uuid' => Str::uuid(),
            'owner_id' => User::factory(),
            'created_by_id' => User::factory(),
            'ticket_id' => null,
            'title' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'due_at' => fake()->dateTimeBetween(now(), '+30 days'),
            'due_in_working_time' => false,
            'branch_id' => null,
            'state' => AgentTaskState::Open,
            'completed_at' => null,
            'cancelled_at' => null,
            'reminder_at' => null,
            'reminder_sent_at' => null,
        ];
    }

    public function withTicket(): static
    {
        return $this->state(function () {
            return [
                'ticket_id' => Ticket::factory(),
            ];
        });
    }

    public function completed(): static
    {
        return $this->state(function () {
            return [
                'state' => AgentTaskState::Done,
                'completed_at' => now(),
            ];
        });
    }

    public function overdue(): static
    {
        return $this->state(function () {
            return [
                'due_at' => now()->subDays(1),
                'state' => AgentTaskState::Open,
            ];
        });
    }
}
