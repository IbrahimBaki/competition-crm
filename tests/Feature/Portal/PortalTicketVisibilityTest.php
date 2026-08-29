<?php

namespace Tests\Feature\Portal;

use App\Domains\Customers\Models\Customer;
use App\Domains\Organisation\Models\Department;
use App\Domains\Portal\Models\PortalAccount;
use App\Domains\Ticketing\Models\Ticket;
use App\Domains\Ticketing\Models\TicketMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class PortalTicketVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_cannot_access_other_customer_ticket(): void
    {
        $customer1 = Customer::forceCreate(['id' => 1, 'uuid' => Str::uuid(), 'name' => 'Customer 1', 'status' => 'active', 'created_at' => now(), 'updated_at' => now()]);
        $customer2 = Customer::forceCreate(['id' => 2, 'uuid' => Str::uuid(), 'name' => 'Customer 2', 'status' => 'active', 'created_at' => now(), 'updated_at' => now()]);

        $account1 = PortalAccount::create([
            'uuid' => (string) Str::uuid(),
            'email' => 'user1@example.com',
            'password' => bcrypt('password'),
            'customer_id' => $customer1->id,
            'email_verified_at' => now(),
        ]);

        $ticket2 = Ticket::factory()->create(['customer_id' => $customer2->id]);

        $token = $account1->createToken('portal', ['portal'])->plainTextToken;

        $this->withHeader('Authorization', "Bearer $token")
            ->getJson("/api/v1/portal/tickets/{$ticket2->uuid}")
            ->assertStatus(404)
            ->assertJsonPath('error.code', 'not_found');
    }

    public function test_ticket_listing_shows_only_own_tickets(): void
    {
        $customer1 = Customer::forceCreate(['id' => 3, 'uuid' => Str::uuid(), 'name' => 'Customer 3', 'status' => 'active', 'created_at' => now(), 'updated_at' => now()]);
        $customer2 = Customer::forceCreate(['id' => 4, 'uuid' => Str::uuid(), 'name' => 'Customer 4', 'status' => 'active', 'created_at' => now(), 'updated_at' => now()]);

        $account1 = PortalAccount::create([
            'uuid' => (string) Str::uuid(),
            'email' => 'user2@example.com',
            'password' => bcrypt('password'),
            'customer_id' => $customer1->id,
            'email_verified_at' => now(),
        ]);

        Ticket::factory(3)->create(['customer_id' => $customer1->id]);
        Ticket::factory(2)->create(['customer_id' => $customer2->id]);

        $token = $account1->createToken('portal', ['portal'])->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/v1/portal/tickets');

        $response->assertStatus(200);
        $this->assertCount(3, $response->json('data'));
    }

    public function test_portal_account_can_create_ticket_and_reply_without_exposing_internal_messages(): void
    {
        Department::factory()->create(['is_active' => true]);
        $customer = Customer::factory()->create();
        $account = PortalAccount::create([
            'uuid' => (string) Str::uuid(),
            'email' => 'portal-workflow@example.com',
            'password' => bcrypt('password'),
            'customer_id' => $customer->id,
            'email_verified_at' => now(),
        ]);
        $token = $account->createToken('portal', ['portal'])->plainTextToken;

        $created = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/v1/portal/tickets', [
                'subject' => 'Cannot sign in',
                'message' => 'The reset link does not work.',
            ]);

        $created->assertCreated()->assertJsonPath('data.subject', 'Cannot sign in');
        $ticketUuid = $created->json('data.uuid');

        $this->withHeader('Authorization', "Bearer $token")
            ->postJson("/api/v1/portal/tickets/{$ticketUuid}/messages", [
                'body' => 'This is still happening.',
            ])->assertCreated()->assertJsonPath('data.author_type', 'customer');

        $ticket = Ticket::where('uuid', $ticketUuid)->firstOrFail();
        TicketMessage::create([
            'ticket_id' => $ticket->id,
            'direction' => 'outbound',
            'author_type' => 'agent',
            'channel' => 'internal',
            'is_internal' => true,
            'body' => 'Private staff note',
        ]);

        $messages = $this->withHeader('Authorization', "Bearer $token")
            ->getJson("/api/v1/portal/tickets/{$ticketUuid}/messages");

        $messages->assertOk();
        $this->assertCount(2, $messages->json('data'));
        $this->assertNotContains('Private staff note', collect($messages->json('data'))->pluck('body')->all());
    }
}
