<?php

namespace App\Domains\Security\Exceptions;

use App\Support\Errors\Contracts\HasApiErrorCode;
use App\Support\Errors\ErrorCode;

class AccountLockedException extends \DomainException implements HasApiErrorCode
{
    public function errorCode(): ErrorCode
    {
        return ErrorCode::AccountLocked;
    }

    public function errorMeta(): array
    {
        return [];
    }
}
