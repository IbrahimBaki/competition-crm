<?php

namespace Tests\Feature\Customers;

use App\Domains\Customers\Models\CustomerDuplicateCandidate;
use App\Domains\Customers\Models\DuplicateCandidateStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CustomerDuplicateReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_list_duplicates_with_pagination(): void
    {
        $customer1 = $this->seed()->factory('customer')->create();
        $customer2 = $this->seed()->factory('customer')->create();

        CustomerDuplicateCandidate::create([
            'uuid' => Str::uuid(),
            'customer_id' => min($customer1->id, $customer2->id),
            'duplicate_customer_id' => max($customer1->id, $customer2->id),
            'status' => DuplicateCandidateStatus::Pending->value,
            'rule' => 'exact_identity',
            'evidence' => [],
        ]);

        $response = $this->getJson('/api/v1/customers/duplicates');

        $response->assertStatus(200);
        $response->assertJsonStructure(['data' => [['uuid', 'customer', 'duplicate_customer', 'status', 'rule', 'evidence']]]);
        $this->assertCount(1, $response->json('data'));
    }

    public function test_duplicate_list_requires_permission(): void
    {
        $userWithoutPermission = $this->seed()->factory('user')->create();
        $this->actingAs($userWithoutPermission);

        $response = $this->getJson('/api/v1/customers/duplicates');

        $response->assertStatus(403);
    }

    public function test_dismiss_duplicate_candidate(): void
    {
        $customer1 = $this->seed()->factory('customer')->create();
        $customer2 = $this->seed()->factory('customer')->create();

        $candidate = CustomerDuplicateCandidate::create([
            'uuid' => Str::uuid(),
            'customer_id' => min($customer1->id, $customer2->id),
            'duplicate_customer_id' => max($customer1->id, $customer2->id),
            'status' => DuplicateCandidateStatus::Pending->value,
            'rule' => 'exact_identity',
            'evidence' => [],
        ]);

        $response = $this->postJson("/api/v1/customers/duplicates/{$candidate->uuid}/dismiss");

        $response->assertStatus(200);
        $this->assertEquals(
            DuplicateCandidateStatus::Dismissed->value,
            $response->json('data.status')
        );
        $this->assertNotNull($response->json('data.reviewed_at'));
    }

    public function test_dismiss_requires_review_permission(): void
    {
        $customer1 = $this->seed()->factory('customer')->create();
        $customer2 = $this->seed()->factory('customer')->create();

        $candidate = CustomerDuplicateCandidate::create([
            'uuid' => Str::uuid(),
            'customer_id' => min($customer1->id, $customer2->id),
            'duplicate_customer_id' => max($customer1->id, $customer2->id),
            'status' => DuplicateCandidateStatus::Pending->value,
            'rule' => 'exact_identity',
            'evidence' => [],
        ]);

        $userWithViewOnly = $this->seed()->factory('user')->create();
        // Grant only VIEW permission, not REVIEW
        $this->actingAs($userWithViewOnly);

        $response = $this->postJson("/api/v1/customers/duplicates/{$candidate->uuid}/dismiss");

        $response->assertStatus(403);
    }

    public function test_filter_duplicates_by_status(): void
    {
        $customer1 = $this->seed()->factory('customer')->create();
        $customer2 = $this->seed()->factory('customer')->create();

        CustomerDuplicateCandidate::create([
            'uuid' => Str::uuid(),
            'customer_id' => min($customer1->id, $customer2->id),
            'duplicate_customer_id' => max($customer1->id, $customer2->id),
            'status' => DuplicateCandidateStatus::Pending->value,
            'rule' => 'exact_identity',
            'evidence' => [],
        ]);

        CustomerDuplicateCandidate::create([
            'uuid' => Str::uuid(),
            'customer_id' => $customer1->id,
            'duplicate_customer_id' => $customer2->id,
            'status' => DuplicateCandidateStatus::Dismissed->value,
            'rule' => 'exact_identity',
            'evidence' => [],
        ]);

        $response = $this->getJson('/api/v1/customers/duplicates?status=pending');

        $this->assertCount(1, $response->json('data'));
        $this->assertEquals(DuplicateCandidateStatus::Pending->value, $response->json('data.0.status'));
    }
}
