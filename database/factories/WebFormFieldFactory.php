<?php

namespace Database\Factories;

use App\Domains\Channels\WebForm\Models\WebForm;
use App\Domains\Channels\WebForm\Models\WebFormField;
use App\Domains\Channels\WebForm\Models\WebFormFieldType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class WebFormFieldFactory extends Factory
{
    protected $model = WebFormField::class;

    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'web_form_id' => WebForm::factory(),
            'key' => $this->faker->slug(),
            'type' => WebFormFieldType::Text,
            'is_required' => false,
            'label' => ['en' => $this->faker->word(), 'ar' => 'حقل'],
            'options' => null,
            'validation' => null,
            'maps_to' => null,
            'position' => 1,
        ];
    }
}
