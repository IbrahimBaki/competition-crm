<?php

namespace Tests\Unit\Customers;

use App\Domains\Customers\Models\ContactType;
use App\Domains\Customers\Models\Customer;
use App\Domains\Customers\Services\Identity\CustomerIdentityResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerIdentityResolverTest extends TestCase
{
    use RefreshDatabase;

    private CustomerIdentityResolver $resolver;

    protected function setUp(): void
    {
        parent::setUp();
        $this->resolver = app(CustomerIdentityResolver::class);
    }

    public function test_resolve_with_no_identities_returns_null(): void
    {
        $resolution = $this->resolver->resolve([]);
        $this->assertNull($resolution->customer);
        $this->assertFalse($resolution->ambiguous);
        $this->assertEmpty($resolution->evidence);
    }

    public function test_resolve_with_unknown_identity_returns_null(): void
    {
        $resolution = $this->resolver->resolve([
            ['type' => 'email', 'value' => 'unknown@example.com'],
        ]);
        $this->assertNull($resolution->customer);
        $this->assertFalse($resolution->ambiguous);
        $this->assertEmpty($resolution->evidence);
    }

    public function test_resolve_with_known_email_returns_owning_customer(): void
    {
        $customer = Customer::factory()->create();
        $customer->contacts()->create([
            'type' => ContactType::Email->value,
            'value' => 'test@example.com',
            'value_normalised' => 'test@example.com',
        ]);

        $resolution = $this->resolver->resolve([
            ['type' => 'email', 'value' => 'test@example.com'],
        ]);

        $this->assertNotNull($resolution->customer);
        $this->assertEquals($customer->id, $resolution->customer->id);
        $this->assertFalse($resolution->ambiguous);
        $this->assertNotEmpty($resolution->evidence);
        $this->assertEquals('exact_identity', $resolution->evidence[0]['rule']);
    }

    public function test_resolve_skips_non_resolvable_identities(): void
    {
        $resolution = $this->resolver->resolve([
            ['type' => 'web_form', 'value' => 'form_value'],
        ]);

        $this->assertNull($resolution->customer);
        $this->assertFalse($resolution->ambiguous);
        $this->assertEmpty($resolution->evidence);
    }

    public function test_resolve_with_ambiguous_identities_returns_no_customer(): void
    {
        $customer1 = Customer::factory()->create();
        $customer2 = Customer::factory()->create();

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

        $resolution = $this->resolver->resolve([
            ['type' => 'email', 'value' => 'test@example.com'],
            ['type' => 'phone', 'value' => '0501234567'],
        ]);

        $this->assertNull($resolution->customer);
        $this->assertTrue($resolution->ambiguous);
        $this->assertCount(2, $resolution->evidence);
    }

    public function test_resolve_returns_evidence_with_rule_names(): void
    {
        $customer = Customer::factory()->create();
        $customer->contacts()->create([
            'type' => ContactType::Phone->value,
            'value' => '0501234567',
            'value_normalised' => '+9660501234567',
        ]);

        $resolution = $this->resolver->resolve([
            ['type' => 'phone', 'value' => '0501234567'],
        ]);

        $this->assertNotNull($resolution->customer);
        $this->assertFalse($resolution->ambiguous);
        $this->assertEquals('normalised_phone', $resolution->evidence[0]['rule']);
    }

    public function test_resolve_normalises_phone_numbers(): void
    {
        $customer = Customer::factory()->create();
        $customer->contacts()->create([
            'type' => ContactType::Phone->value,
            'value' => '+966501234567',
            'value_normalised' => '+966501234567',
        ]);

        // Different format, same number
        $resolution = $this->resolver->resolve([
            ['type' => 'phone', 'value' => '0501234567'],
        ]);

        $this->assertNotNull($resolution->customer);
        $this->assertEquals($customer->id, $resolution->customer->id);
        $this->assertFalse($resolution->ambiguous);
    }
}
