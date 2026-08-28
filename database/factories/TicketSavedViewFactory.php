<?php

namespace Database\Factories;

use App\Domains\Ticketing\Models\TicketSavedView;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TicketSavedView>
 */
class TicketSavedViewFactory extends Factory
{
    protected $model = TicketSavedView::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => $this->faker->unique()->words(3, true),
            'query' => ['filter' => ['status' => 'open']],
            'is_shared' => false,
        ];
    }
}
