<?php

namespace App\Domains\Sla\Exceptions;

use App\Support\Http\Errors\ErrorCode;
use App\Support\Http\Errors\HasApiErrorCode;
use Exception;

class TargetAlreadyExhaustedException extends Exception implements HasApiErrorCode
{
    public function errorCode(): ErrorCode
    {
        return ErrorCode::TargetAlreadyExhausted;
    }

    public function errorMeta(): array
    {
        return [];
    }
}
