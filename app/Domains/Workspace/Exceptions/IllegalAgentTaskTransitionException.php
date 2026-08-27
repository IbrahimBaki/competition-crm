<?php

namespace App\Domains\Workspace\Exceptions;

use App\Support\Http\Errors\ErrorCode;
use App\Support\Http\Errors\HasApiErrorCode;
use Exception;

class IllegalAgentTaskTransitionException extends Exception implements HasApiErrorCode
{
    public function errorCode(): ErrorCode
    {
        return ErrorCode::IllegalAgentTaskTransition;
    }

    public function errorMeta(): array
    {
        return [];
    }
}
