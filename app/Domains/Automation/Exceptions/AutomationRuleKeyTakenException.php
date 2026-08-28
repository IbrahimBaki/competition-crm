<?php

namespace App\Domains\Automation\Exceptions;

use App\Support\Http\Errors\ErrorCode;
use App\Support\Http\Errors\HasApiErrorCode;
use Exception;

class AutomationRuleKeyTakenException extends Exception implements HasApiErrorCode
{
    public function errorCode(): ErrorCode
    {
        return ErrorCode::AutomationRuleKeyTaken;
    }

    public function errorMeta(): array
    {
        return [];
    }
}
