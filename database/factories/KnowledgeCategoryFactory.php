<?php

namespace Database\Factories;

use App\Domains\Knowledge\Models\KnowledgeCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

class KnowledgeCategoryFactory extends Factory
{
    protected $model = KnowledgeCategory::class;

    public function definition(): array
    {
        return [
            'uuid' => $this->faker->uuid(),
            'parent_id' => null,
            'code' => $this->faker->unique()->slug(2),
            'name' => [
                'ar' => $this->faker->word(),
                'en' => $this->faker->word(),
            ],
            'depth' => 0,
            'position' => 0,
            'is_active' => true,
        ];
    }

    public function withParent(KnowledgeCategory $parent): self
    {
        return $this->state([
            'parent_id' => $parent->id,
            'depth' => $parent->depth + 1,
        ]);
    }

    public function inactive(): self
    {
        return $this->state(['is_active' => false]);
    }
}
