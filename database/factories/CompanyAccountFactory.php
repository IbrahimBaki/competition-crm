<?php

namespace Database\Factories;

use App\Domains\Customers\Models\CompanyAccount;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CompanyAccountFactory extends Factory
{
    protected $model = CompanyAccount::class;

    public function definition(): array
    {
        return [
            'id' => Str::uuid(),
            'name' => fake()->company(),
            'name_normalised' => fake()->word(),
            'service_tier' => fake()->randomElement(['standard', 'priority', 'vip']),
        ];
    }
}
