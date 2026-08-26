<?php

namespace App\Domains\Security\Exceptions;

use App\Support\Http\Errors\HasApiErrorCode;
use App\Support\Http\Errors\ErrorCode;

class AccountLockedException extends \DomainException implements HasApiErrorCode
{
    public function __construct()
    {
        parent::__construct(__('errors.security.account_locked'));
    }

    public function errorCode(): ErrorCode
    {
        return ErrorCode::AccountLocked;
    }

    public function errorMeta(): array
    {
        return [];
    }
}
