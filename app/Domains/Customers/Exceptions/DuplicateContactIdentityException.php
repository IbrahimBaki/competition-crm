<?php

namespace App\Domains\Customers\Exceptions;

use App\Support\Http\Errors\ErrorCode;
use App\Support\Http\Errors\HasApiErrorCode;
use Exception;

class DuplicateContactIdentityException extends Exception implements HasApiErrorCode
{
    public function errorCode(): ErrorCode
    {
        return ErrorCode::DuplicateContactIdentity;
    }

    public function errorMeta(): array
    {
        return [];
    }
}
