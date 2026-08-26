<?php

namespace App\Domains\Customers\Services\Identity;

use App\Domains\Customers\Actions\FlagDuplicateCustomers;
use App\Domains\Customers\Models\ContactType;
use App\Domains\Customers\Models\Customer;
use App\Domains\Customers\Models\CustomerContact;
use App\Domains\Customers\Services\TextNormaliser;

final class CustomerIdentityResolver
{
    public function __construct(
        private readonly TextNormaliser $normaliser,
        private readonly FlagDuplicateCustomers $flagDuplicates,
    ) {}

    /** @param array<int, array{type: string, value: string}> $identities */
    public function resolve(array $identities): IdentityResolution
    {
        $evidenceByCustomerId = [];

        foreach ($identities as $identity) {
            try {
                $type = ContactType::from($identity['type']);
            } catch (\ValueError) {
                continue;
            }

            if (! $type->isResolvable()) {
                continue;
            }

            $normalised = $this->normaliser->normaliseContact($identity['value'], $type);

            if (empty($normalised)) {
                continue;
            }

            $contacts = CustomerContact::where('type', $type->value)
                ->where('value_normalised', $normalised)
                ->get();

            foreach ($contacts as $contact) {
                $customerId = $contact->customer_id;
                if (! isset($evidenceByCustomerId[$customerId])) {
                    $evidenceByCustomerId[$customerId] = [];
                }

                $rule = $type->isPhoneLike() ? 'normalised_phone' : 'exact_identity';
                $evidenceByCustomerId[$customerId][] = [
                    'type' => $type->value,
                    'normalised' => $normalised,
                    'customer_uuid' => $contact->customer->uuid,
                    'rule' => $rule,
                ];
            }
        }

        $distinctCustomerIds = array_keys($evidenceByCustomerId);

        if (empty($distinctCustomerIds)) {
            return new IdentityResolution(null, false, []);
        }

        if (count($distinctCustomerIds) === 1) {
            $customer = Customer::find($distinctCustomerIds[0]);

            return new IdentityResolution($customer, false, $evidenceByCustomerId[$distinctCustomerIds[0]]);
        }

        // Multiple customers — ambiguous match
        $evidence = [];
        foreach ($distinctCustomerIds as $customerId) {
            $evidence = array_merge($evidence, $evidenceByCustomerId[$customerId]);
        }

        // Get the first customer to use as the primary key (lowest id)
        $primaryCustomerId = min($distinctCustomerIds);
        $duplicateCustomerId = null;

        // For now, use the second customer as duplicate (will be normalized in action)
        foreach ($distinctCustomerIds as $cid) {
            if ($cid !== $primaryCustomerId) {
                $duplicateCustomerId = $cid;
                break;
            }
        }

        if ($duplicateCustomerId) {
            $this->flagDuplicates->execute(
                Customer::find($primaryCustomerId),
                Customer::find($duplicateCustomerId),
                'normalised_phone'
            );
        }

        return new IdentityResolution(null, true, $evidence);
    }
}
