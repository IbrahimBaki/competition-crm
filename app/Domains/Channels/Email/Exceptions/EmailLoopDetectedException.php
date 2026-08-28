<?php

namespace App\Domains\Channels\Email\Exceptions;

use App\Support\Http\Errors\ErrorCode;
use App\Support\Http\Errors\HasApiErrorCode;

class EmailLoopDetectedException extends \Exception implements HasApiErrorCode
{
    public function errorCode(): ErrorCode
    {
        return ErrorCode::EmailLoopDetected;
    }

    public function errorMeta(): array
    {
        return [];
    }
}
