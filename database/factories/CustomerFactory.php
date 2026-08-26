<?php

namespace Database\Factories;

use App\Domains\Customers\Models\Customer;
use App\Domains\Customers\Services\ArabicTextNormaliser;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    public function definition(): array
    {
        $normaliser = new ArabicTextNormaliser;
        $name = fake()->name();

        return [
            'id' => Str::uuid(),
            'name' => $name,
            'name_normalised' => $normaliser->normaliseName($name),
            'preferred_locale' => fake()->randomElement(['ar', 'en']),
            'status' => 'active',
        ];
    }

    public function arabic(): static
    {
        $normaliser = new ArabicTextNormaliser;

        return $this->state(function () use ($normaliser) {
            $arabicNames = ['أحمد', 'فاطمة', 'محمد', 'نور', 'ليلى', 'عمر', 'هند'];
            $name = fake()->randomElement($arabicNames);

            return [
                'name' => $name,
                'name_normalised' => $normaliser->normaliseName($name),
                'preferred_locale' => 'ar',
            ];
        });
    }
}
