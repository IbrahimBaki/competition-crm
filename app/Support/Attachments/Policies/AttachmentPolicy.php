<?php

namespace App\Support\Attachments\Policies;

use App\Models\User;
use App\Support\Attachments\Attachment;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Gate;

class AttachmentPolicy
{
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
