<?php

namespace App\Domains\Security\Exceptions;

use App\Support\Errors\Contracts\HasApiErrorCode;
use App\Support\Errors\ErrorCode;

class TwoFactorAlreadyEnabledException extends \DomainException implements HasApiErrorCode
{
    public function __construct()
    {
        parent::__construct(__('errors.security.two_factor_already_enabled'));
    }

    public function errorCode(): ErrorCode
    {
        return ErrorCode::TwoFactorAlreadyEnabled;
    }

    public function errorMeta(): array
    {
        return [];
    }
}
