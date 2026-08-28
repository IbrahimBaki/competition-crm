<?php

namespace Tests\Feature\Ticketing;

use App\Domains\Ticketing\Models\TicketStatus;
use App\Domains\Ticketing\Models\TicketStatusDefinition;
use App\Models\User;
use Tests\TestCase;

class TicketStatusCatalogueTest extends TestCase
{
    public function test_administrator_can_rename_system_status(): void
    {
        $admin = User::factory()->create();
        $status = TicketStatusDefinition::where('is_system', true)->first();

        $response = $this->actingAs($admin)->patchJson("/api/v1/ticket-statuses/{$status->uuid}", [
            'name' => [
                'en' => 'In Progress',
                'ar' => 'قيد المعالجة',
            ],
        ]);

        $response->assertOk();
        $this->assertEquals('In Progress', $status->fresh()->name['en']);
    }

    public function test_create_status_with_valid_lifecycle_type(): void
    {
        $admin = User::factory()->create();

        $response = $this->actingAs($admin)->postJson('/api/v1/ticket-statuses', [
            'name' => [
                'en' => 'On Hold',
                'ar' => 'معلق',
            ],
            'lifecycle_type' => TicketStatus::Pending->value,
            'position' => 10,
            'is_active' => true,
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('ticket_statuses', [
            'lifecycle_type' => 'pending',
        ]);
    }
}
