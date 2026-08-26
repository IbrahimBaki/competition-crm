<?php

namespace App\Domains\Security\Exceptions;

use App\Support\Http\Errors\HasApiErrorCode;
use App\Support\Http\Errors\ErrorCode;

class InvitationAlreadyPendingException extends \DomainException implements HasApiErrorCode
{
    public function __construct(string $email)
    {
        parent::__construct(__('errors.security.invitation_already_pending', ['email' => $email]));
    }

    public function errorCode(): ErrorCode
    {
        return ErrorCode::InvitationAlreadyPending;
    }

    public function errorMeta(): array
    {
        return [];
    }
}
