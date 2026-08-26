<?php

namespace App\Domains\Sla\Exceptions;

use App\Support\Http\Errors\ErrorCode;
use App\Support\Http\Errors\HasApiErrorCode;
use Exception;

class SlaBreachImmutableException extends Exception implements HasApiErrorCode
{
    public function errorCode(): ErrorCode
    {
        return ErrorCode::SlaBreachImmutable;
    }

    public function errorMeta(): array
    {
        return [];
    }
}
