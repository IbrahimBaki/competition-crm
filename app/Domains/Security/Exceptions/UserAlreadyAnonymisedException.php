<?php

namespace App\Domains\Security\Exceptions;

use App\Support\Http\Errors\ErrorCode;
use App\Support\Http\Errors\HasApiErrorCode;

class UserAlreadyAnonymisedException extends \Exception implements HasApiErrorCode
{
    public function errorCode(): ErrorCode
    {
        return ErrorCode::UserAlreadyAnonymised;
    }

    public function errorMeta(): array
    {
        return [];
    }
}
