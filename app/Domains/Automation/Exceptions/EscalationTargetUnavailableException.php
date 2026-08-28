<?php

namespace App\Domains\Automation\Exceptions;

use App\Support\Http\Errors\ErrorCode;
use App\Support\Http\Errors\HasApiErrorCode;
use Exception;

class EscalationTargetUnavailableException extends Exception implements HasApiErrorCode
{
    public function errorCode(): ErrorCode
    {
        return ErrorCode::AutomationEscalationTargetUnavailable;
    }

    public function errorMeta(): array
    {
        return [];
    }
}
