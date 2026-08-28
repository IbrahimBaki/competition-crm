<?php

namespace Database\Factories;

use App\Domains\Channels\Chat\Models\ChatVisitorIdentity;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ChatVisitorIdentityFactory extends Factory
{
    protected $model = ChatVisitorIdentity::class;

    public function definition(): array
    {
        return [
            'visitor_token' => Str::random(32),
            'customer_id' => null,
            'customer_contact_id' => null,
            'display_name' => $this->faker->name(),
            'email' => $this->faker->email(),
            'phone' => $this->faker->phoneNumber(),
            'normalised_email' => strtolower($this->faker->email()),
            'normalised_phone' => preg_replace('/\D/', '', $this->faker->phoneNumber()),
            'locale' => 'en',
            'user_agent' => $this->faker->userAgent(),
        ];
    }
}
