<?php

namespace App\Domains\Security\Exceptions;

use App\Support\Errors\Contracts\HasApiErrorCode;
use App\Support\Errors\ErrorCode;

class InvitationAlreadyPendingException extends \DomainException implements HasApiErrorCode
{
    public function errorCode(): ErrorCode
    {
        return ErrorCode::InvitationAlreadyPending;
    }

    public function errorMeta(): array
    {
        return [];
    }
}
