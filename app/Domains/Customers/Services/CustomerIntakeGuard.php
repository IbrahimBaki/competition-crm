<?php

namespace App\Domains\Customers\Services;

use App\Domains\Customers\Exceptions\CustomerBlockedException;
use App\Domains\Customers\Models\Customer;
use App\Domains\Customers\Models\CustomerStatus;

class CustomerIntakeGuard
{
    public function assertCanOpenTicket(Customer $customer): void
    {
        if ($customer->status === CustomerStatus::Blocked) {
            throw new CustomerBlockedException($customer->blocked_reason ?? 'No reason provided');
        }
    }
}
