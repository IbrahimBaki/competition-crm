<?php

namespace Database\Factories;

use App\Domains\Ticketing\Models\MessageAuthorType;
use App\Domains\Ticketing\Models\MessageChannel;
use App\Domains\Ticketing\Models\MessageDeliveryState;
use App\Domains\Ticketing\Models\MessageDirection;
use App\Domains\Ticketing\Models\TicketMessage;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class TicketMessageFactory extends Factory
{
    protected $model = TicketMessage::class;

    public function definition(): array
    {
        return [
            'uuid' => Str::uuid(),
            'ticket_id' => TicketFactory::new(),
            'direction' => MessageDirection::Outbound,
            'author_type' => MessageAuthorType::Agent,
            'author_user_id' => UserFactory::new(),
            'channel' => MessageChannel::Email,
            'is_internal' => false,
            'body' => fake()->paragraph(),
            'body_format' => 'text',
            'delivery_state' => MessageDeliveryState::Queued,
            'queued_at' => now(),
        ];
    }

    public function internal(): static
    {
        return $this->state(function () {
            return [
                'channel' => MessageChannel::Internal,
                'is_internal' => true,
                'delivery_state' => null,
                'queued_at' => null,
            ];
        });
    }

    public function inbound(): static
    {
        return $this->state(function () {
            return [
                'direction' => MessageDirection::Inbound,
                'author_type' => MessageAuthorType::Customer,
                'author_user_id' => null,
                'delivery_state' => null,
            ];
        });
    }

    public function failed(): static
    {
        return $this->state(function () {
            return [
                'delivery_state' => MessageDeliveryState::Failed,
                'failure_reason' => 'provider_rejected',
                'failed_at' => now(),
                'queued_at' => now()->subMinute(),
            ];
        });
    }

    public function queued(): static
    {
        return $this->state(function () {
            return [
                'delivery_state' => MessageDeliveryState::Queued,
                'queued_at' => now(),
            ];
        });
    }
}
