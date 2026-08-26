<?php

namespace Database\Factories;

use App\Domains\Ticketing\Models\TicketCategory;
use App\Support\I18n\BilingualString;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class TicketCategoryFactory extends Factory
{
    protected $model = TicketCategory::class;

    public function definition(): array
    {
        return [
            'uuid' => Str::uuid(),
            'parent_id' => null,
            'code' => fake()->slug(2, '-'),
            'name' => new BilingualString(
                fake()->sentence(2),
                fake()->sentence(2),
            ),
            'depth' => 1,
            'position' => 0,
            'is_active' => true,
        ];
    }

    public function withParent(TicketCategory $parent): static
    {
        return $this->state(function () use ($parent) {
            return [
                'parent_id' => $parent->id,
                'depth' => $parent->depth + 1,
            ];
        });
    }
}
