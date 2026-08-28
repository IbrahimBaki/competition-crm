<?php

namespace Database\Factories;

use App\Domains\Channels\WebForm\Models\WebForm;
use App\Domains\Organisation\Models\Department;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class WebFormFactory extends Factory
{
    protected $model = WebForm::class;

    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'key' => $this->faker->slug(),
            'title' => ['en' => $this->faker->sentence(), 'ar' => 'نموذج'],
            'description' => ['en' => $this->faker->text(), 'ar' => 'وصف'],
            'department_id' => Department::factory(),
            'ticket_category_id' => null,
            'default_priority' => 'normal',
            'is_active' => true,
        ];
    }
}
