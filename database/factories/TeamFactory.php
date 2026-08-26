<?php

namespace Database\Factories;

use App\Domains\Organisation\Models\Department;
use App\Domains\Organisation\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Team>
 */
class TeamFactory extends Factory
{
    protected $model = Team::class;

    public function definition(): array
    {
        $name = fake()->word();

        return [
            'id' => Str::uuid(),
            'department_id' => Department::factory(),
            'name' => [
                'ar' => fake()->word(),
                'en' => $name,
            ],
            'code' => strtolower($name),
            'is_active' => true,
        ];
    }
}
