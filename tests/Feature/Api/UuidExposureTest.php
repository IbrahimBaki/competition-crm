<?php

namespace Tests\Feature\Api;

use App\Domains\Customers\Models\CustomerDuplicateCandidate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class UuidExposureTest extends TestCase
{
    use RefreshDatabase;

    public function test_list_response_ids_are_uuid(): void
    {
        $response = $this->getJson('/api/v1/branches');

        $response->assertStatus(200);
        $items = $response->json('data');

        foreach ($items as $item) {
            $this->assertIsString($item['id']);
            $this->assertMatchesRegularExpression('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $item['id']);
        }
    }

    public function test_single_item_id_is_uuid(): void
    {
        $branch = $this->seed()->factory('branch')->create();

        $response = $this->getJson('/api/v1/branches/'.$branch->id);

        $response->assertStatus(200);
        $this->assertMatchesRegularExpression('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $response->json('data.id'));
    }

    public function test_timestamps_are_iso8601_with_offset(): void
    {
        $response = $this->getJson('/api/v1/branches');

        $response->assertStatus(200);
        $items = $response->json('data');

        foreach ($items as $item) {
            if (isset($item['created_at'])) {
                $this->assertMatchesRegularExpression('/T.*(\+|-)\d{2}:\d{2}$/', $item['created_at']);
            }
            if (isset($item['updated_at'])) {
                $this->assertMatchesRegularExpression('/T.*(\+|-)\d{2}:\d{2}$/', $item['updated_at']);
            }
        }
    }

    public function test_single_item_timestamps_are_iso8601(): void
    {
        $branch = $this->seed()->factory('branch')->create();

        $response = $this->getJson('/api/v1/branches/'.$branch->id);

        $response->assertStatus(200);
        $item = $response->json('data');

        if (isset($item['created_at'])) {
            $this->assertMatchesRegularExpression('/T.*(\+|-)\d{2}:\d{2}$/', $item['created_at']);
        }
    }

    public function test_no_numeric_ids_in_responses(): void
    {
        $response = $this->getJson('/api/v1/branches');

        $response->assertStatus(200);
        $items = $response->json('data');

        foreach ($items as $item) {
            $this->assertIsString($item['id']);
            $this->assertFalse(is_numeric($item['id']) && strpos($item['id'], '-') === false);
        }
    }

    public function test_duplicate_candidate_list_uses_uuids(): void
    {
        $customer1 = $this->seed()->factory('customer')->create();
        $customer2 = $this->seed()->factory('customer')->create();

        CustomerDuplicateCandidate::create([
            'uuid' => Str::uuid(),
            'customer_id' => min($customer1->id, $customer2->id),
            'duplicate_customer_id' => max($customer1->id, $customer2->id),
            'status' => 'pending',
            'rule' => 'exact_identity',
            'evidence' => [],
        ]);

        $response = $this->getJson('/api/v1/customers/duplicates');

        $response->assertStatus(200);
        $items = $response->json('data');

        foreach ($items as $item) {
            // Main UUID
            $this->assertIsString($item['uuid']);
            $this->assertMatchesRegularExpression('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $item['uuid']);

            // Customer UUIDs in nested objects
            $this->assertIsString($item['customer']['uuid']);
            $this->assertMatchesRegularExpression('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $item['customer']['uuid']);

            $this->assertIsString($item['duplicate_customer']['uuid']);
            $this->assertMatchesRegularExpression('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $item['duplicate_customer']['uuid']);

            // Should NOT have numeric ids
            $this->assertArrayNotHasKey('id', $item);
        }
    }

    public function test_merge_response_uses_uuids(): void
    {
        $survivor = $this->seed()->factory('customer')->create();
        $loser = $this->seed()->factory('customer')->create();

        $response = $this->postJson(
            "/api/v1/customers/{$survivor->uuid}/merge",
            ['duplicate_customer_uuid' => $loser->uuid]
        );

        $response->assertStatus(200);
        $item = $response->json('data');

        // Main UUID
        $this->assertIsString($item['uuid']);
        $this->assertMatchesRegularExpression('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $item['uuid']);

        // Should NOT have numeric id
        $this->assertArrayNotHasKey('id', $item);
    }

    public function test_ticket_messages_use_uuids(): void
    {
        $ticket = $this->seed()->factory('ticket')->create();
        $user = $this->seed()->factory('user')->create();
        $user->grantPermission('ticket.message.view');
        $user->grantPermission('ticket.message.send');

        $this->actingAs($user)->postJson(
            "/api/v1/tickets/{$ticket->uuid}/messages",
            [
                'body' => 'Test message',
                'channel' => 'email',
            ]
        );

        $response = $this->actingAs($user)->getJson("/api/v1/tickets/{$ticket->uuid}/messages");

        $response->assertStatus(200);
        $messages = $response->json('data');

        foreach ($messages as $message) {
            // Main UUID
            $this->assertIsString($message['uuid']);
            $this->assertMatchesRegularExpression('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $message['uuid']);

            // Should NOT have numeric id
            $this->assertArrayNotHasKey('id', $message);
        }
    }

    public function test_ticket_message_delivery_events_use_uuids(): void
    {
        $ticket = $this->seed()->factory('ticket')->create();
        $user = $this->seed()->factory('user')->create();
        $user->grantPermission('ticket.message.view');
        $user->grantPermission('ticket.message.send');

        $postResponse = $this->actingAs($user)->postJson(
            "/api/v1/tickets/{$ticket->uuid}/messages",
            [
                'body' => 'Test message',
                'channel' => 'email',
            ]
        );

        $messageUuid = $postResponse->json('data.uuid');

        $response = $this->actingAs($user)->getJson(
            "/api/v1/tickets/{$ticket->uuid}/messages/{$messageUuid}/delivery-events"
        );

        $response->assertStatus(200);
        $events = $response->json('data');

        foreach ($events as $event) {
            // Event UUID
            $this->assertIsString($event['uuid']);
            $this->assertMatchesRegularExpression('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $event['uuid']);

            // Should NOT have numeric id
            $this->assertArrayNotHasKey('id', $event);
        }
    }
}
