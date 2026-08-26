<?php

namespace Database\Factories;

use App\Domains\Organisation\Models\Branch;
use App\Domains\Organisation\Models\BranchWorkingHour;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BranchWorkingHour>
 */
class BranchWorkingHourFactory extends Factory
{
    protected $model = BranchWorkingHour::class;

    public function definition(): array
    {
        return [
            'branch_id' => Branch::factory(),
            'day_of_week' => fake()->numberBetween(0, 6),
            'is_working' => true,
            'opens_at' => '09:00:00',
            'closes_at' => '17:00:00',
        ];
    }

    public function notWorking(): static
    {
        return $this->state(fn () => [
            'is_working' => false,
            'opens_at' => null,
            'closes_at' => null,
        ]);
    }
}
