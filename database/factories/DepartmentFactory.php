<?php

namespace Database\Factories;

use App\Domains\Organisation\Models\Branch;
use App\Domains\Organisation\Models\Department;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Department>
 */
class DepartmentFactory extends Factory
{
    protected $model = Department::class;

    public function definition(): array
    {
        $name = fake()->word();

        return [
            'id' => Str::uuid(),
            'branch_id' => Branch::factory(),
            'name' => [
                'ar' => fake()->word(),
                'en' => $name,
            ],
            'code' => strtolower($name),
            'is_active' => true,
        ];
    }
}
