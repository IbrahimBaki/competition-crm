<?php

namespace App\Domains\Sla\Exceptions;

use App\Support\Http\Errors\ErrorCode;
use App\Support\Http\Errors\HasApiErrorCode;
use Exception;

class SlaClockNotRunningException extends Exception implements HasApiErrorCode
{
    public function errorCode(): ErrorCode
    {
        return ErrorCode::SlaClockNotRunning;
    }

    public function errorMeta(): array
    {
        return [];
    }
}
