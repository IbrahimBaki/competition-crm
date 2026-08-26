<?php

namespace Database\Factories;

use App\Domains\Organisation\Models\Branch;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Branch>
 */
class BranchFactory extends Factory
{
    protected $model = Branch::class;

    public function definition(): array
    {
        $name = fake()->word();

        return [
            'id' => Str::uuid(),
            'name' => [
                'ar' => fake()->word(),
                'en' => $name,
            ],
            'code' => strtolower($name),
            'timezone' => 'UTC',
            'is_24_7' => false,
            'is_active' => true,
        ];
    }
}
