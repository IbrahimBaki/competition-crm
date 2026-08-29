<?php

namespace App\Support\Attachments\Policies;

use App\Domains\Security\Permissions\PermissionKey;
use App\Models\User;
use App\Support\Attachments\Attachment;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Gate;

class AttachmentPolicy
{
    public function viewAny(User $user): Response
    {
        return $user->can(PermissionKey::CUSTOMERS_VIEW)
            ? Response::allow()
            : Response::deny();
    }

    public function create(User $user): Response
    {
        return $user->can(PermissionKey::CUSTOMERS_ATTACHMENT_MANAGE)
            ? Response::allow()
            : Response::deny();
    }

    public function delete(User $user, Attachment $attachment): Response
    {
        return $user->can(PermissionKey::CUSTOMERS_ATTACHMENT_MANAGE)
            ? Response::allow()
            : Response::deny();
    }

    public function download(User $user, Attachment $attachment): Response
    {
        if (! $attachment->attachable) {
            return $user->id === $attachment->uploaded_by
                ? Response::allow()
                : Response::deny();
        }

        return Gate::allows('view', $attachment->attachable)
            ? Response::allow()
            : Response::deny();
    }
}
