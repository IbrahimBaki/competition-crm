<?php

namespace Database\Factories;

use App\Domains\Customers\Services\ArabicTextNormaliser;
use App\Domains\Ticketing\Models\TicketTag;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class TicketTagFactory extends Factory
{
    protected $model = TicketTag::class;

    public function definition(): array
    {
        $normaliser = new ArabicTextNormaliser;
        $name = fake()->word();

        return [
            'uuid' => Str::uuid(),
            'name' => $name,
            'name_normalised' => $normaliser->normaliseName($name),
        ];
    }
}
