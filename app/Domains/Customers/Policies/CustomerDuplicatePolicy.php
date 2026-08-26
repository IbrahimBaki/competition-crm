<?php

namespace App\Domains\Customers\Policies;

use App\Domains\Customers\Models\CustomerDuplicateCandidate;
use App\Domains\Security\Permissions\PermissionKey;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CustomerDuplicatePolicy
{
    public function viewAny(User $user): Response
    {
        return $user->can(PermissionKey::CUSTOMERS_DUPLICATE_VIEW)
            ? Response::allow()
            : Response::deny();
    }

    public function view(User $user, CustomerDuplicateCandidate $candidate): Response
    {
        return $user->can(PermissionKey::CUSTOMERS_DUPLICATE_VIEW)
            ? Response::allow()
            : Response::deny();
    }

    public function dismissDuplicate(User $user, CustomerDuplicateCandidate $candidate): Response
    {
        return $user->can(PermissionKey::CUSTOMERS_DUPLICATE_REVIEW)
            ? Response::allow()
            : Response::deny();
    }
}
