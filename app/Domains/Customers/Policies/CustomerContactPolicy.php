<?php

namespace App\Domains\Customers\Policies;

use App\Domains\Customers\Models\CustomerContact;
use App\Domains\Customers\Models\CustomerStatus;
use App\Domains\Security\Permissions\PermissionKey;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CustomerContactPolicy
{
    public function viewAny(User $user): Response
    {
        return $user->can(PermissionKey::CUSTOMERS_VIEW)
            ? Response::allow()
            : Response::deny();
    }

    public function view(User $user, CustomerContact $contact): Response
    {
        return $user->can(PermissionKey::CUSTOMERS_VIEW)
            ? Response::allow()
            : Response::deny();
    }

    public function create(User $user): Response
    {
        return $user->can(PermissionKey::CUSTOMERS_CONTACT_MANAGE)
            ? Response::allow()
            : Response::deny();
    }

    public function update(User $user, CustomerContact $contact): Response
    {
        if ($contact->customer->status === CustomerStatus::Anonymised) {
            return Response::deny();
        }

        return $user->can(PermissionKey::CUSTOMERS_CONTACT_MANAGE)
            ? Response::allow()
            : Response::deny();
    }

    public function delete(User $user, CustomerContact $contact): Response
    {
        if ($contact->customer->status === CustomerStatus::Anonymised) {
            return Response::deny();
        }

        return $user->can(PermissionKey::CUSTOMERS_CONTACT_MANAGE)
            ? Response::allow()
            : Response::deny();
    }
}
