<?php

namespace App\Domains\Security\Exceptions;

use App\Support\Http\Errors\ErrorCode;
use App\Support\Http\Errors\HasApiErrorCode;
use Exception;

class AdministratorRoleLockedException extends Exception implements HasApiErrorCode
{
    public function __construct(string $message = 'Cannot remove administrative permissions from the administrator role.')
    {
        parent::__construct($message);
    }

    public function errorCode(): ErrorCode
    {
        return ErrorCode::AdminRoleLocked;
    }

    public function errorMeta(): array
    {
        return [];
    }
}
