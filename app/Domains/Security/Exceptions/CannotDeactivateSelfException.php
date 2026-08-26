<?php

namespace App\Domains\Security\Exceptions;

use App\Support\Errors\Contracts\HasApiErrorCode;
use App\Support\Errors\ErrorCode;

class CannotDeactivateSelfException extends \DomainException implements HasApiErrorCode
{
    public function errorCode(): ErrorCode
    {
        return ErrorCode::CannotDeactivateSelf;
    }

    public function errorMeta(): array
    {
        return [];
    }
}
