<?php

namespace Database\Factories;

use App\Domains\Ai\Models\AiFeature;
use App\Domains\Ai\Models\AiSuggestion;
use App\Domains\Ai\Models\AiSuggestionState;
use App\Domains\Ticketing\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AiSuggestionFactory extends Factory
{
    protected $model = AiSuggestion::class;

    public function definition(): array
    {
        return [
            'ticket_id' => Ticket::factory(),
            'feature' => $this->faker->randomElement(AiFeature::cases()),
            'state' => AiSuggestionState::Pending,
            'content' => $this->faker->paragraph(),
            'confidence' => $this->faker->randomFloat(2, 0, 1),
            'model' => 'null',
            'requested_by_user_id' => User::factory(),
            'resolved_by_user_id' => null,
            'resolved_at' => null,
        ];
    }

    public function accepted(): self
    {
        return $this->state([
            'state' => AiSuggestionState::Accepted,
            'resolved_by_user_id' => User::factory(),
            'resolved_at' => now(),
        ]);
    }

    public function sent(): self
    {
        return $this->accepted()->state([
            'state' => AiSuggestionState::Sent,
        ]);
    }
}
