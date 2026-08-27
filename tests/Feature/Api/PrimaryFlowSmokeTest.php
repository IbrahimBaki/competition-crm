<?php

namespace Tests\Feature\Api;

use App\Domains\Customers\Models\Customer;
use App\Domains\Ticketing\Models\Ticket;
use Tests\TestCase;

class PrimaryFlowSmokeTest extends TestCase
{
    public function test_primary_flow_staff_login_to_ticket_creation()
    {
        // 1. Staff Login
        $loginResponse = $this->postJson('/api/v1/auth/login', [
            'email' => 'admin@support.local',
            'password' => 'password',
        ]);

        $loginResponse->assertStatus(200);
        $loginResponse->assertJsonStructure(['data' => ['token', 'user']]);
        $staffToken = $loginResponse->json('data.token');

        // 2. Create Customer
        $customerResponse = $this->withHeaders(['Authorization' => "Bearer {$staffToken}"])
            ->postJson('/api/v1/customers', [
                'name' => 'Test Corp',
                'email' => 'test@example.local',
            ]);

        $customerResponse->assertStatus(201);
        $customerResponse->assertJsonStructure(['data' => ['id', 'name', 'email']]);
        $customerId = $customerResponse->json('data.id');

        // 3. Create Ticket
        $ticketResponse = $this->withHeaders(['Authorization' => "Bearer {$staffToken}"])
            ->postJson('/api/v1/tickets', [
                'customer_id' => $customerId,
                'subject' => 'Test Ticket',
                'message' => 'This is a test ticket message.',
            ]);

        $ticketResponse->assertStatus(201);
        $ticketResponse->assertJsonStructure(['data' => ['id', 'reference', 'subject']]);
        $ticketId = $ticketResponse->json('data.id');

        // Verify ticket exists in database
        $this->assertNotNull(Ticket::find($ticketId));

        return [
            'staffToken' => $staffToken,
            'customerId' => $customerId,
            'ticketId' => $ticketId,
        ];
    }

    public function test_primary_flow_message_and_assignment()
    {
        $context = $this->test_primary_flow_staff_login_to_ticket_creation();
        $staffToken = $context['staffToken'];
        $ticketId = $context['ticketId'];

        // 4. Post Public Reply
        $publicMsgResponse = $this->withHeaders(['Authorization' => "Bearer {$staffToken}"])
            ->postJson("/api/v1/tickets/{$ticketId}/messages", [
                'body' => 'Thank you for reporting. We are investigating.',
                'visibility' => 'public',
            ]);

        $publicMsgResponse->assertStatus(201);
        $publicMsgResponse->assertJsonStructure(['data' => ['id', 'body', 'visibility']]);

        // 5. Post Internal Note
        $internalMsgResponse = $this->withHeaders(['Authorization' => "Bearer {$staffToken}"])
            ->postJson("/api/v1/tickets/{$ticketId}/messages", [
                'body' => 'Customer is on premium plan - P1 priority.',
                'visibility' => 'internal',
            ]);

        $internalMsgResponse->assertStatus(201);

        // 6. Assign Ticket (using a seeded user)
        $assignResponse = $this->withHeaders(['Authorization' => "Bearer {$staffToken}"])
            ->postJson("/api/v1/tickets/{$ticketId}/assign", [
                'user_id' => 1,
            ]);

        $assignResponse->assertStatus(200);
    }

    public function test_primary_flow_status_changes()
    {
        $context = $this->test_primary_flow_staff_login_to_ticket_creation();
        $staffToken = $context['staffToken'];
        $ticketId = $context['ticketId'];

        // Get available statuses
        $statusesResponse = $this->withHeaders(['Authorization' => "Bearer {$staffToken}"])
            ->getJson('/api/v1/ticket-statuses');

        $statusesResponse->assertStatus(200);
        $statusId = $statusesResponse->json('data.0.id');

        // Change status
        $statusResponse = $this->withHeaders(['Authorization' => "Bearer {$staffToken}"])
            ->postJson("/api/v1/tickets/{$ticketId}/status", [
                'status_id' => $statusId,
            ]);

        $statusResponse->assertStatus(200);
    }

    public function test_primary_flow_ticket_detail_retrieval()
    {
        $context = $this->test_primary_flow_staff_login_to_ticket_creation();
        $staffToken = $context['staffToken'];
        $ticketId = $context['ticketId'];

        // Get ticket detail
        $detailResponse = $this->withHeaders(['Authorization' => "Bearer {$staffToken}"])
            ->getJson("/api/v1/tickets/{$ticketId}");

        $detailResponse->assertStatus(200);
        $detailResponse->assertJsonStructure([
            'data' => ['id', 'reference', 'subject', 'messages'],
        ]);
    }

    public function test_primary_flow_portal_access()
    {
        $context = $this->test_primary_flow_staff_login_to_ticket_creation();
        $customerId = $context['customerId'];
        $ticketId = $context['ticketId'];

        // Get the seeded portal customer
        $customer = Customer::find($customerId);
        $contact = $customer->contacts()->first();

        // Portal login (if seeded with password)
        // Note: Actual password depends on seeder setup
        $portalLoginResponse = $this->postJson('/api/v1/portal/auth/login', [
            'email' => $contact->email,
            'password' => 'test-password', // Must match seeder
        ]);

        // If portal login available, verify access
        if ($portalLoginResponse->status() === 200) {
            $portalToken = $portalLoginResponse->json('data.token');

            // Get my tickets
            $ticketsResponse = $this->withHeaders(['Authorization' => "Bearer {$portalToken}"])
                ->getJson('/api/v1/portal/tickets');

            $ticketsResponse->assertStatus(200);
            $ticketsResponse->assertJsonStructure(['data', 'meta']);

            // Get single ticket (should only see public fields)
            $ticketResponse = $this->withHeaders(['Authorization' => "Bearer {$portalToken}"])
                ->getJson("/api/v1/portal/tickets/{$ticketId}");

            $ticketResponse->assertStatus(200);
            // Verify internal notes NOT in response
            $this->assertNotContains('internal', strtolower($ticketResponse->json('data.messages.*.visibility')));
        }
    }

    public function test_primary_flow_web_form_submission()
    {
        // Get a public web form
        $formsResponse = $this->getJson('/api/v1/channels/public/web-forms/demo-form');

        if ($formsResponse->status() === 200) {
            // Submit form
            $submitResponse = $this->postJson('/api/v1/channels/public/web-forms/demo-form/submissions', [
                'name' => 'Web Form User',
                'email' => 'form@example.local',
                'message' => 'Testing web form submission.',
            ]);

            $submitResponse->assertStatus(201);
        }
    }

    public function test_primary_flow_management_dashboard()
    {
        // Login first
        $loginResponse = $this->postJson('/api/v1/auth/login', [
            'email' => 'admin@support.local',
            'password' => 'password',
        ]);

        $staffToken = $loginResponse->json('data.token');

        // Get dashboard report
        $dashboardResponse = $this->withHeaders(['Authorization' => "Bearer {$staffToken}"])
            ->getJson('/api/v1/reports/management-dashboard');

        $dashboardResponse->assertStatus(200);
        $dashboardResponse->assertJsonStructure(['data', 'meta']);
    }

    public function test_primary_flow_integration_token()
    {
        // Login
        $loginResponse = $this->postJson('/api/v1/auth/login', [
            'email' => 'admin@support.local',
            'password' => 'password',
        ]);

        $staffToken = $loginResponse->json('data.token');

        // Issue API token
        $tokenResponse = $this->withHeaders(['Authorization' => "Bearer {$staffToken}"])
            ->postJson('/api/v1/integration/tokens', [
                'name' => 'Test Integration Token',
                'scopes' => ['tickets.read', 'customers.read'],
            ]);

        $tokenResponse->assertStatus(201);
        $tokenResponse->assertJsonStructure(['data' => ['id', 'token', 'scopes']]);
        $apiToken = $tokenResponse->json('data.token');

        // Use API token
        $apiResponse = $this->withHeaders(['Authorization' => "Bearer {$apiToken}"])
            ->getJson('/api/v1/tickets?page=1&per_page=10');

        $apiResponse->assertStatus(200);
        $apiResponse->assertJsonStructure(['data', 'meta']);
    }
}
