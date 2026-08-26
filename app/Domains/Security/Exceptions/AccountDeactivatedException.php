<?php

namespace App\Domains\Security\Exceptions;

use App\Support\Errors\Contracts\HasApiErrorCode;
use App\Support\Errors\ErrorCode;

class AccountDeactivatedException extends \DomainException implements HasApiErrorCode
{
    public function __construct()
    {
        parent::__construct(__('errors.security.account_deactivated'));
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
