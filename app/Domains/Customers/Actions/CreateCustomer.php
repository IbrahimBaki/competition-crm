<?php

namespace App\Domains\Customers\Actions;

use App\Domains\Customers\Models\Customer;
use App\Domains\Customers\Models\CustomerEventType;
use App\Domains\Customers\Services\TextNormaliser;
use App\Domains\Security\Services\AuditLogger;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Str;

class CreateCustomer
{
    public function __construct(
        private readonly TextNormaliser $normaliser,
        private readonly AuditLogger $auditLogger,
    ) {}

    public function execute(array $data, ?Authenticatable $actor = null): Customer
    {
        return \DB::transaction(function () use ($data, $actor) {
            $customer = Customer::create([
                'id' => Str::uuid(),
                'name' => $data['name'],
                'name_normalised' => $this->normaliser->normaliseName($data['name']),
                'company_account_id' => $data['company_account_id'] ?? null,
                'preferred_locale' => $data['preferred_locale'] ?? 'en',
                'status' => 'active',
            ]);

            $customer->events()->create([
                'id' => Str::uuid(),
                'type' => CustomerEventType::Created->value,
                'actor_user_id' => $actor?->id,
                'occurred_at' => now(),
            ]);

            $this->auditLogger->record(
                $actor,
                'customers.customer.created',
                $customer,
                null,
                $customer->toArray()
            );

            return $customer;
        });
    }
}
