<?php

namespace App\Domains\Customers\Services;

use App\Domains\Customers\Exceptions\CustomerBlockedException;
use App\Domains\Customers\Models\Customer;
use App\Domains\Customers\Models\CustomerStatus;
use App\Domains\Customers\Services\Identity\CustomerIdentityResolver;
use App\Domains\Customers\Services\Identity\IdentityResolution;

class CustomerIntakeGuard
{
    public function __construct(
        private readonly CustomerIdentityResolver $identityResolver,
    ) {}

    public function assertCanOpenTicket(Customer $customer): void
    {
        if ($customer->status === CustomerStatus::Blocked) {
            throw new CustomerBlockedException($customer->blocked_reason ?? 'No reason provided');
        }
    }

    /** @param array<int, array{type: string, value: string}> $identities */
    public function resolveIdentities(array $identities): IdentityResolution
    {
        return $this->identityResolver->resolve($identities);
    }
}
