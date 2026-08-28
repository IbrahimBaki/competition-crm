<?php

namespace Database\Factories;

use App\Domains\Customers\Services\ArabicTextNormaliser;
use App\Domains\Organisation\Models\Department;
use App\Domains\Ticketing\Models\Ticket;
use App\Domains\Ticketing\Models\TicketPriority;
use App\Domains\Ticketing\Models\TicketStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class TicketFactory extends Factory
{
    protected $model = Ticket::class;

    public function definition(): array
    {
        $normaliser = new ArabicTextNormaliser;
        $subject = fake()->sentence(3);
        $body = fake()->paragraph();

        return [
            'uuid' => Str::uuid(),
            'reference' => 'TKT-'.date('Ym').'-'.str_pad(fake()->numberBetween(1, 999999), 6, '0', STR_PAD_LEFT),
            'customer_id' => CustomerFactory::new(),
            'department_id' => Department::factory(),
            'ticket_category_id' => null,
            'assigned_user_id' => null,
            'created_by_user_id' => User::factory(),
            'subject' => $subject,
            'subject_normalised' => $normaliser->normaliseName($subject),
            'body' => $body,
            'body_normalised' => $normaliser->normaliseName($body),
            'status' => TicketStatus::New,
            'priority' => fake()->randomElement(TicketPriority::cases()),
            'custom_fields' => null,
        ];
    }

    public function assigned(): static
    {
        return $this->state(function () {
            return [
                'assigned_user_id' => User::factory(),
            ];
        });
    }

    public function withCategory(): static
    {
        return $this->state(function () {
            return [
                'ticket_category_id' => TicketCategoryFactory::new(),
            ];
        });
    }
}
