<?php

namespace App\Domains\Security\Exceptions;

use App\Support\Http\Errors\ErrorCode;
use App\Support\Http\Errors\HasApiErrorCode;

class InvitationExpiredException extends \DomainException implements HasApiErrorCode
{
    public function __construct()
    {
        parent::__construct(__('errors.security.invitation_expired'));
    }

    public function errorCode(): ErrorCode
    {
        return ErrorCode::InvitationExpired;
    }

    public function errorMeta(): array
    {
        return [];
    }
}
