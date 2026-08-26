<?php

namespace App\Domains\Security\Exceptions;

use App\Support\Errors\Contracts\HasApiErrorCode;
use App\Support\Errors\ErrorCode;

class UserAlreadyDeactivatedException extends \DomainException implements HasApiErrorCode
{
    public function __construct()
    {
        parent::__construct(__('errors.security.user_already_deactivated'));
    }

    public function errorCode(): ErrorCode
    {
        return ErrorCode::AccountDeactivated;
    }

    public function errorMeta(): array
    {
        return [];
    }
}
