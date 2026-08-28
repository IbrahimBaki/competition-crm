<?php

namespace Database\Factories;

use App\Domains\Customers\Models\CustomerNote;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CustomerNoteFactory extends Factory
{
    protected $model = CustomerNote::class;

    public function definition(): array
    {
        return [
            'id' => Str::uuid(),
            'body' => fake()->paragraph(),
        ];
    }
}
