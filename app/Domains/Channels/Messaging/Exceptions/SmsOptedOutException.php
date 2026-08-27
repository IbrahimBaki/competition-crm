<?php

namespace App\Domains\Channels\Messaging\Exceptions;

use App\Support\Http\Errors\ErrorCode;
use App\Support\Http\Errors\HasApiErrorCode;
use Exception;

class SmsOptedOutException extends Exception implements HasApiErrorCode
{
    public function errorCode(): ErrorCode
    {
        return ErrorCode::SmsOptedOut;
    }

    public function getPayload(): array
    {
        return [];
    }
}
