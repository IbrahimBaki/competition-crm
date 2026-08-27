<?php

namespace Database\Factories;

use App\Domains\Organisation\Models\Department;
use App\Domains\Workspace\Models\QuickReply;
use App\Domains\Workspace\Models\QuickReplyScope;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class QuickReplyFactory extends Factory
{
    protected $model = QuickReply::class;

    public function definition(): array
    {
        return [
            'uuid' => Str::uuid(),
            'scope' => QuickReplyScope::Personal,
            'owner_id' => User::factory(),
            'department_id' => null,
            'title' => ['ar' => 'رد سريع', 'en' => 'Quick Reply'],
            'body' => ['ar' => 'شكراً على رسالتك', 'en' => 'Thank you for your message'],
            'is_active' => true,
        ];
    }

    public function shared(): static
    {
        return $this->state(function () {
            return [
                'scope' => QuickReplyScope::Shared,
                'owner_id' => null,
                'department_id' => Department::factory(),
            ];
        });
    }

    public function withPlaceholders(): static
    {
        return $this->state(function () {
            return [
                'body' => ['ar' => 'السلام عليكم {{customer_name}}، رقم التذكرة {{ticket_reference}}', 'en' => 'Hello {{customer_name}}, your ticket {{ticket_reference}} is being handled by {{agent_name}}'],
            ];
        });
    }
}
