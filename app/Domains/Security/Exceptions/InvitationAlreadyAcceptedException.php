<?php

namespace App\Domains\Security\Exceptions;

use App\Support\Http\Errors\ErrorCode;
use App\Support\Http\Errors\HasApiErrorCode;

class InvitationAlreadyAcceptedException extends \DomainException implements HasApiErrorCode
{
    public function __construct()
    {
        parent::__construct(__('errors.security.invitation_already_accepted'));
    }

    public function errorCode(): ErrorCode
    {
        return ErrorCode::InvitationInvalid;
    }

    public function errorMeta(): array
    {
        return [];
    }
}
