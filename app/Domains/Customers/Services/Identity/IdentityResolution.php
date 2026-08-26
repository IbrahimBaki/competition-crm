<?php

namespace App\Domains\Customers\Services\Identity;

use App\Domains\Customers\Models\Customer;

final class IdentityResolution
{
    public function __construct(
        public readonly ?Customer $customer,
        public readonly bool $ambiguous,
        /** @var array<int, array{type: string, normalised: string, customer_uuid: string, rule: string}> */
        public readonly array $evidence,
    ) {}
}
