<?php

namespace App\Domains\Customers\Policies;

use App\Domains\Customers\Models\CustomerNote;
use App\Domains\Customers\Models\CustomerStatus;
use App\Domains\Security\Permissions\PermissionKey;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CustomerNotePolicy
{
    public function viewAny(User $user): Response
    {
        return $user->can(PermissionKey::CUSTOMERS_NOTE_VIEW)
            ? Response::allow()
            : Response::deny();
    }

    public function view(User $user, CustomerNote $note): Response
    {
        return $user->can(PermissionKey::CUSTOMERS_NOTE_VIEW)
            ? Response::allow()
            : Response::deny();
    }

    public function create(User $user): Response
    {
        return $user->can(PermissionKey::CUSTOMERS_NOTE_CREATE)
            ? Response::allow()
            : Response::deny();
    }

    public function delete(User $user, CustomerNote $note): Response
    {
        if ($note->customer->status === CustomerStatus::Anonymised) {
            return Response::deny();
        }

        return $user->can(PermissionKey::CUSTOMERS_NOTE_DELETE)
            ? Response::allow()
            : Response::deny();
    }
}
