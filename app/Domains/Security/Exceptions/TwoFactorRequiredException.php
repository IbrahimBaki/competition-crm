<?php

namespace App\Domains\Security\Exceptions;

use App\Support\Errors\Contracts\HasApiErrorCode;
use App\Support\Errors\ErrorCode;

class TwoFactorRequiredException extends \DomainException implements HasApiErrorCode
{
    public function __construct()
    {
        parent::__construct(__('errors.security.two_factor_required'));
    }

    public function errorCode(): ErrorCode
    {
        return ErrorCode::TwoFactorRequired;
    }

    public function errorMeta(): array
    {
        return [];
    }
}
