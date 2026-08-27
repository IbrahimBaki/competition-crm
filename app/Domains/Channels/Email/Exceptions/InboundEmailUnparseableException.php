<?php

namespace App\Domains\Channels\Email\Exceptions;

use App\Support\Http\Errors\ErrorCode;
use App\Support\Http\Errors\HasApiErrorCode;

class InboundEmailUnparseableException extends \Exception implements HasApiErrorCode
{
    public function errorCode(): ErrorCode
    {
        return ErrorCode::EmailInboundUnparseable;
    }

    public function errorMeta(): array
    {
        return [];
    }
}
