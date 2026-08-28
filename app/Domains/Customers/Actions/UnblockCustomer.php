<?php

namespace App\Domains\Customers\Actions;

use App\Domains\Customers\Exceptions\CustomerNotBlockedException;
use App\Domains\Customers\Models\Customer;
use App\Domains\Customers\Models\CustomerEventType;
use App\Domains\Customers\Models\CustomerStatus;
use App\Domains\Security\Services\AuditLogger;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Str;

class UnblockCustomer
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function execute(Customer $customer, ?Authenticatable $actor = null): Customer
    {
        if (! $customer->isBlocked()) {
            throw new CustomerNotBlockedException('Customer is not blocked');
        }

        return \DB::transaction(function () use ($customer, $actor) {
            $before = $customer->toArray();

            $customer->status = CustomerStatus::Active;
            $customer->blocked_reason = null;
            $customer->blocked_at = null;
            $customer->blocked_by_user_id = null;
            $customer->save();

            $customer->events()->create([
                'id' => Str::uuid(),
                'type' => CustomerEventType::Unblocked->value,
                'actor_user_id' => $actor?->id,
                'occurred_at' => now(),
            ]);

            $this->auditLogger->record(
                $actor,
                'customers.customer.unblocked',
                $customer,
                $before,
                $customer->toArray()
            );

            return $customer;
        });
    }
}
