<?php

namespace Database\Factories;

use App\Domains\Customers\Models\CustomerContact;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CustomerContactFactory extends Factory
{
    protected $model = CustomerContact::class;

    public function definition(): array
    {
        return [
            'id' => Str::uuid(),
            'type' => fake()->randomElement(['email', 'phone', 'whatsapp', 'portal_login']),
            'value' => fake()->email(),
            'value_normalised' => fake()->email(),
            'label' => fake()->optional()->word(),
            'is_primary' => false,
        ];
    }
}
