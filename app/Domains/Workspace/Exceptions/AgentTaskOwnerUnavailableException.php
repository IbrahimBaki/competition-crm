<?php

namespace App\Domains\Workspace\Exceptions;

use App\Support\Http\Errors\ErrorCode;
use App\Support\Http\Errors\HasApiErrorCode;
use Exception;

class AgentTaskOwnerUnavailableException extends Exception implements HasApiErrorCode
{
    public function errorCode(): ErrorCode
    {
        return ErrorCode::AgentTaskOwnerUnavailable;
    }

    public function errorMeta(): array
    {
        return [];
    }
}
