<?php

namespace App\Domains\Integrations\Exceptions;

use App\Support\Http\Errors\ErrorCode;
use App\Support\Http\Errors\HasApiErrorCode;
use Exception;

final class ApiTokenAlreadyRevokedException extends Exception implements HasApiErrorCode
{
    public function errorCode(): ErrorCode
    {
        return ErrorCode::Unauthorized;
    }

    public function errorMeta(): array
    {
        return [];
    }
}
