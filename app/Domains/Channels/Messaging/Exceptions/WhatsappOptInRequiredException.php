<?php

namespace App\Domains\Channels\Messaging\Exceptions;

use App\Support\Http\Errors\ErrorCode;
use App\Support\Http\Errors\HasApiErrorCode;
use Exception;

class WhatsappOptInRequiredException extends Exception implements HasApiErrorCode
{
    public function errorCode(): ErrorCode
    {
        return ErrorCode::WhatsappOptInRequired;
    }

    public function getPayload(): array
    {
        return [];
    }
}
