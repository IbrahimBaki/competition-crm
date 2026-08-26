<?php

namespace App\Domains\Organisation\Exceptions;

use App\Support\Http\Errors\ErrorCode;
use App\Support\Http\Errors\HasApiErrorCode;
use Exception;

class UserBranchNotAttachedException extends Exception implements HasApiErrorCode
{
    public function __construct()
    {
        parent::__construct('User is not attached to this branch', 422);
    }

    public function errorCode(): ErrorCode
    {
        return ErrorCode::UserBranchNotAttached;
    }

    public function errorMeta(): array
    {
        return [];
    }
}
