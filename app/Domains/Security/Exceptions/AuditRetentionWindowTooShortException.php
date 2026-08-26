<?php

namespace App\Domains\Security\Exceptions;

use App\Support\Http\Errors\ErrorCode;
use App\Support\Http\Errors\HasApiErrorCode;

class AuditRetentionWindowTooShortException extends \Exception implements HasApiErrorCode
{
    public function errorCode(): ErrorCode
    {
        return ErrorCode::AuditRetentionWindowTooShort;
    }

    public function errorMeta(): array
    {
        return [];
    }
}
