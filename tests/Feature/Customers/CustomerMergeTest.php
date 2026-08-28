<?php

namespace Tests\Feature\Customers;

use App\Domains\Customers\Models\ContactType;
use App\Domains\Customers\Models\Customer;
use App\Domains\Customers\Models\CustomerEvent;
use App\Domains\Customers\Models\CustomerNote;
use App\Domains\Security\Models\AuditLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerMergeTest extends TestCase
{
    use RefreshDatabase;

    public function test_merge_moves_notes_to_survivor(): void
    {
        $survivor = Customer::factory()->create(['name' => 'Survivor']);
        $loser = Customer::factory()->create(['name' => 'Loser']);

        $loser->notes()->create([
            'content' => ['en' => 'Loser note', 'ar' => 'ملاحظة الخاسر'],
            'created_by_user_id' => auth()->id(),
        ]);

        $response = $this->postJson(
            "/api/v1/customers/{$survivor->uuid}/merge",
            ['duplicate_customer_uuid' => $loser->uuid]
        );

        $response->assertStatus(200);

        // Assert no notes reference the loser
        $this->assertCount(0, CustomerNote::where('customer_id', $loser->id)->get());
        // Assert note moved to survivor
        $this->assertCount(1, CustomerNote::where('customer_id', $survivor->id)->get());
    }

    public function test_merge_moves_events_to_survivor(): void
    {
        $survivor = Customer::factory()->create(['name' => 'Survivor']);
        $loser = Customer::factory()->create(['name' => 'Loser']);

        $loser->events()->create([
            'type' => 'note_added',
            'payload' => [],
            'occurred_at' => now(),
        ]);

        $this->postJson(
            "/api/v1/customers/{$survivor->uuid}/merge",
            ['duplicate_customer_uuid' => $loser->uuid]
        );

        // Assert no events reference the loser except MergedInto event
        $this->assertCount(
            1,
            CustomerEvent::where('customer_id', $loser->id)->get()
        );
        // Assert survivor has the moved event + Merged event
        $this->assertGreaterThanOrEqual(1, CustomerEvent::where('customer_id', $survivor->id)->count());
    }

    public function test_merge_moves_contacts_to_survivor(): void
    {
        $survivor = Customer::factory()->create(['name' => 'Survivor']);
        $loser = Customer::factory()->create(['name' => 'Loser']);

        $loser->contacts()->create([
            'type' => ContactType::Email->value,
            'value' => 'loser@example.com',
            'value_normalised' => 'loser@example.com',
        ]);

        $this->postJson(
            "/api/v1/customers/{$survivor->uuid}/merge",
            ['duplicate_customer_uuid' => $loser->uuid]
        );

        // Assert contact moved to survivor
        $this->assertCount(1, $survivor->fresh()->contacts);
        $this->assertCount(0, $loser->fresh()->contacts);
    }

    public function test_merge_deletes_duplicate_contacts(): void
    {
        $survivor = Customer::factory()->create(['name' => 'Survivor']);
        $loser = Customer::factory()->create(['name' => 'Loser']);

        $survivor->contacts()->create([
            'type' => ContactType::Email->value,
            'value' => 'test@example.com',
            'value_normalised' => 'test@example.com',
            'is_primary' => true,
        ]);

        $loser->contacts()->create([
            'type' => ContactType::Email->value,
            'value' => 'test@example.com',
            'value_normalised' => 'test@example.com',
        ]);

        $this->postJson(
            "/api/v1/customers/{$survivor->uuid}/merge",
            ['duplicate_customer_uuid' => $loser->uuid]
        );

        // Only one email contact should remain (survivor's)
        $emails = $survivor->fresh()->contacts()->where('type', ContactType::Email->value)->get();
        $this->assertCount(1, $emails);
    }

    public function test_old_customer_uuid_resolves_to_survivor(): void
    {
        $survivor = Customer::factory()->create(['name' => 'Survivor']);
        $loser = Customer::factory()->create(['name' => 'Loser']);

        $this->postJson(
            "/api/v1/customers/{$survivor->uuid}/merge",
            ['duplicate_customer_uuid' => $loser->uuid]
        );

        $response = $this->getJson("/api/v1/customers/{$loser->uuid}");

        $response->assertStatus(200);
        $this->assertEquals($survivor->uuid, $response->json('data.uuid'));
    }

    public function test_merge_records_audit_entry(): void
    {
        $survivor = Customer::factory()->create(['name' => 'Survivor']);
        $loser = Customer::factory()->create(['name' => 'Loser']);

        $this->postJson(
            "/api/v1/customers/{$survivor->uuid}/merge",
            ['duplicate_customer_uuid' => $loser->uuid]
        );

        // Check audit log
        $auditLogs = AuditLog::where('action', 'customers.merged')->get();
        $this->assertCount(1, $auditLogs);
        $this->assertEquals($survivor->id, $auditLogs[0]->subject_id);
    }

    public function test_self_merge_fails(): void
    {
        $customer = Customer::factory()->create(['name' => 'Customer']);

        $response = $this->postJson(
            "/api/v1/customers/{$customer->uuid}/merge",
            ['duplicate_customer_uuid' => $customer->uuid]
        );

        $response->assertStatus(409);
    }

    public function test_merge_already_merged_customer_fails(): void
    {
        $survivor = Customer::factory()->create(['name' => 'Survivor']);
        $loser1 = Customer::factory()->create(['name' => 'Loser1']);
        $loser2 = Customer::factory()->create(['name' => 'Loser2']);

        // First merge
        $this->postJson(
            "/api/v1/customers/{$survivor->uuid}/merge",
            ['duplicate_customer_uuid' => $loser1->uuid]
        );

        // Try to merge an already-merged customer
        $response = $this->postJson(
            "/api/v1/customers/{$survivor->uuid}/merge",
            ['duplicate_customer_uuid' => $loser1->uuid]
        );

        $response->assertStatus(409);
    }
}
