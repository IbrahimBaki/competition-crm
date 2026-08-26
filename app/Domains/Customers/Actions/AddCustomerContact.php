<?php

namespace App\Domains\Customers\Actions;

use App\Domains\Customers\Exceptions\DuplicateContactIdentityException;
use App\Domains\Customers\Models\ContactType;
use App\Domains\Customers\Models\Customer;
use App\Domains\Customers\Models\CustomerContact;
use App\Domains\Customers\Models\CustomerEventType;
use App\Domains\Customers\Services\TextNormaliser;
use App\Domains\Security\Services\AuditLogger;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\QueryException;
use Illuminate\Support\Str;

class AddCustomerContact
{
    public function __construct(
        private readonly TextNormaliser $normaliser,
        private readonly AuditLogger $auditLogger,
    ) {}

    public function execute(
        Customer $customer,
        string $type,
        string $value,
        ?string $label = null,
        bool $isPrimary = false,
        ?Authenticatable $actor = null,
    ): CustomerContact {
        $contactType = ContactType::from($type);
        $normalizedValue = $this->normaliser->normaliseContact($value, $contactType);

        return \DB::transaction(function () use (
            $customer,
            $contactType,
            $value,
            $normalizedValue,
            $label,
            $isPrimary,
            $actor,
        ) {
            try {
                $contact = CustomerContact::create([
                    'id' => Str::uuid(),
                    'customer_id' => $customer->id,
                    'type' => $contactType->value,
                    'value' => $value,
                    'value_normalised' => $normalizedValue,
                    'label' => $label,
                    'is_primary' => $isPrimary,
                ]);
            } catch (QueryException $e) {
                if ($e->getCode() === '23000') {
                    throw new DuplicateContactIdentityException(
                        "Contact identity already in use: {$contactType->value}={$normalizedValue}"
                    );
                }

                throw $e;
            }

            if ($isPrimary) {
                $actorId = $actor instanceof User ? $actor->id : null;
                CustomerContact::where('customer_id', $customer->id)
                    ->where('type', $contactType->value)
                    ->where('id', '!=', $contact->id)
                    ->update(['is_primary' => false]);
            }

            $customer->events()->create([
                'id' => Str::uuid(),
                'type' => CustomerEventType::ContactAdded->value,
                'actor_user_id' => $actor?->id,
                'payload' => ['contact_type' => $contactType->value],
                'occurred_at' => now(),
            ]);

            $this->auditLogger->record(
                $actor,
                'customers.contact.added',
                $customer,
                null,
                $contact->toArray()
            );

            return $contact;
        });
    }
}
