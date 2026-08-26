<?php

namespace App\Domains\Customers\Actions;

use App\Domains\Customers\Exceptions\CustomerMustHaveContactException;
use App\Domains\Customers\Models\Customer;
use App\Domains\Customers\Models\CustomerContact;
use App\Domains\Customers\Models\CustomerEventType;
use App\Domains\Customers\Models\CustomerStatus;
use App\Domains\Security\Services\AuditLogger;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Str;

class RemoveCustomerContact
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function execute(
        Customer $customer,
        CustomerContact $contact,
        ?Authenticatable $actor = null,
    ): void {
        if (
            $customer->contacts()->count() === 1
            && $customer->status !== CustomerStatus::Anonymised
        ) {
            throw new CustomerMustHaveContactException(
                'Customer must have at least one contact'
            );
        }

        \DB::transaction(function () use ($customer, $contact, $actor) {
            $before = $contact->toArray();
            $contact->delete();

            $customer->events()->create([
                'id' => Str::uuid(),
                'type' => CustomerEventType::ContactRemoved->value,
                'actor_user_id' => $actor?->id,
                'payload' => ['contact_type' => $contact->type->value],
                'occurred_at' => now(),
            ]);

            $this->auditLogger->record(
                $actor,
                'customers.contact.removed',
                $customer,
                $before,
                null
            );
        });
    }
}
