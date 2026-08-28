<?php

namespace Database\Factories;

use App\Domains\Ticketing\Models\TicketStatus;
use App\Domains\Ticketing\Models\TicketStatusDefinition;
use Illuminate\Database\Eloquent\Factories\Factory;

class TicketStatusDefinitionFactory extends Factory
{
    protected $model = TicketStatusDefinition::class;

    public function definition(): array
    {
        $type = $this->faker->randomElement([
            TicketStatus::New,
            TicketStatus::Open,
            TicketStatus::Pending,
            TicketStatus::Resolved,
            TicketStatus::Closed,
            TicketStatus::Spam,
        ]);

        return [
            'name' => [
                'en' => ucfirst($type->value),
                'ar' => match ($type->value) {
                    'new' => 'جديد',
                    'open' => 'مفتوح',
                    'pending' => 'قيد الانتظار',
                    'resolved' => 'تم الحل',
                    'closed' => 'مغلق',
                    'spam' => 'بريد عشوائي',
                },
            ],
            'lifecycle_type' => $type,
            'is_system' => false,
            'is_active' => true,
            'position' => 0,
        ];
    }
}
