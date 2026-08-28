<?php

namespace App\Domains\Customers\Policies;

use App\Domains\Customers\Models\Customer;
use App\Domains\Customers\Models\CustomerStatus;
use App\Domains\Security\Permissions\PermissionKey;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CustomerPolicy
{
    public function viewAny(User $user): Response
    {
        return $user->can(PermissionKey::CUSTOMERS_VIEW)
            ? Response::allow()
            : Response::deny();
    }

    public function view(User $user, Customer $customer): Response
    {
        return $user->can(PermissionKey::CUSTOMERS_VIEW)
            ? Response::allow()
            : Response::deny();
    }

    public function create(User $user): Response
    {
        return $user->can(PermissionKey::CUSTOMERS_CREATE)
            ? Response::allow()
            : Response::deny();
    }

    public function update(User $user, Customer $customer): Response
    {
        if ($customer->status === CustomerStatus::Anonymised) {
            return Response::deny();
        }

        return $user->can(PermissionKey::CUSTOMERS_UPDATE)
            ? Response::allow()
            : Response::deny();
    }

    public function block(User $user, Customer $customer): Response
    {
        return $user->can(PermissionKey::CUSTOMERS_BLOCK)
            ? Response::allow()
            : Response::deny();
    }

    public function unblock(User $user, Customer $customer): Response
    {
        return $user->can(PermissionKey::CUSTOMERS_BLOCK)
            ? Response::allow()
            : Response::deny();
    }

    public function merge(User $user, Customer $customer): Response
    {
        return $user->can(PermissionKey::CUSTOMERS_MERGE)
            ? Response::allow()
            : Response::deny();
    }
}
