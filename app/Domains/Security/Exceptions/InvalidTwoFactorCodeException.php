<?php

namespace App\Domains\Security\Exceptions;

use App\Support\Errors\Contracts\HasApiErrorCode;
use App\Support\Errors\ErrorCode;

class InvalidTwoFactorCodeException extends \DomainException implements HasApiErrorCode
{
    public function __construct()
    {
        parent::__construct(__('errors.security.invalid_two_factor_code'));
    }

    public function errorCode(): ErrorCode
    {
        return ErrorCode::InvalidTwoFactorCode;
    }

    public function errorMeta(): array
    {
        return [];
    }
}
