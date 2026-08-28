<?php

namespace App\Domains\Customers\Actions;

use App\Domains\Customers\Exceptions\CustomerAlreadyBlockedException;
use App\Domains\Customers\Models\Customer;
use App\Domains\Customers\Models\CustomerEventType;
use App\Domains\Customers\Models\CustomerStatus;
use App\Domains\Security\Services\AuditLogger;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Str;

class BlockCustomer
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function execute(Customer $customer, string $reason, ?Authenticatable $actor = null): Customer
    {
        if ($customer->isBlocked()) {
            throw new CustomerAlreadyBlockedException('Customer is already blocked');
        }

        return \DB::transaction(function () use ($customer, $reason, $actor) {
            $before = $customer->toArray();

            $customer->status = CustomerStatus::Blocked;
            $customer->blocked_reason = $reason;
            $customer->blocked_at = now();
            $customer->blocked_by_user_id = $actor?->id;
            $customer->save();

            $customer->events()->create([
                'id' => Str::uuid(),
                'type' => CustomerEventType::Blocked->value,
                'actor_user_id' => $actor?->id,
                'payload' => ['reason' => $reason],
                'occurred_at' => now(),
            ]);

            $this->auditLogger->record(
                $actor,
                'customers.customer.blocked',
                $customer,
                $before,
                $customer->toArray()
            );

            return $customer;
        });
    }
}
