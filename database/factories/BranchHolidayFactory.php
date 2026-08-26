<?php

namespace Database\Factories;

use App\Domains\Organisation\Models\Branch;
use App\Domains\Organisation\Models\BranchHoliday;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<BranchHoliday>
 */
class BranchHolidayFactory extends Factory
{
    protected $model = BranchHoliday::class;

    public function definition(): array
    {
        return [
            'id' => Str::uuid(),
            'branch_id' => Branch::factory(),
            'name' => [
                'ar' => fake()->word(),
                'en' => fake()->word(),
            ],
            'date' => fake()->date(),
            'recurring_month_day' => null,
        ];
    }

    public function recurring(): static
    {
        return $this->state(fn () => [
            'date' => null,
            'recurring_month_day' => sprintf('%02d-%02d', fake()->numberBetween(1, 12), fake()->numberBetween(1, 28)),
        ]);
    }
}
