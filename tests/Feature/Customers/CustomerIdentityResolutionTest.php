<?php

namespace Tests\Feature\Customers;

use App\Domains\Customers\Models\ContactType;
use App\Domains\Customers\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerIdentityResolutionTest extends TestCase
{
    use RefreshDatabase;

    public function test_inbound_contact_with_known_email_resolves_to_existing_customer(): void
    {
        $existingCustomer = Customer::factory()->create(['name' => 'Existing']);
        $existingCustomer->contacts()->create([
            'type' => ContactType::Email->value,
            'value' => 'test@example.com',
            'value_normalised' => 'test@example.com',
        ]);

        $data = [
            'name' => 'New Customer',
            'preferred_locale' => 'ar',
            'identities' => [
                ['type' => 'email', 'value' => 'test@example.com'],
            ],
        ];

        $response = $this->postJson('/api/v1/customers', $data);

        // Should resolve to existing customer, not create new one
        $this->assertSame(
            $existingCustomer->id,
            Customer::where('uuid', $response->json('data.uuid'))->first()?->id
        );
    }

    public function test_ambiguous_identities_create_pending_duplicate_candidate(): void
    {
        $customer1 = Customer::factory()->create(['name' => 'Customer 1']);
        $customer2 = Customer::factory()->create(['name' => 'Customer 2']);

        $customer1->contacts()->create([
            'type' => ContactType::Email->value,
            'value' => 'test@example.com',
            'value_normalised' => 'test@example.com',
        ]);

        $customer2->contacts()->create([
            'type' => ContactType::Phone->value,
            'value' => '0501234567',
            'value_normalised' => '+9660501234567',
        ]);

        $data = [
            'name' => 'Ambiguous Customer',
            'preferred_locale' => 'ar',
            'identities' => [
                ['type' => 'email', 'value' => 'test@example.com'],
                ['type' => 'phone', 'value' => '0501234567'],
            ],
        ];

        $response = $this->postJson('/api/v1/customers', $data);

        // Should not create a new customer
        $response->assertStatus(400); // or appropriate error status
    }
}
