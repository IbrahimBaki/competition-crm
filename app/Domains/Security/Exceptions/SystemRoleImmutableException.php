<?php

namespace App\Domains\Security\Exceptions;

use App\Support\Http\Errors\ErrorCode;
use App\Support\Http\Errors\HasApiErrorCode;
use Exception;

class SystemRoleImmutableException extends Exception implements HasApiErrorCode
{
    public function __construct(string $message = 'System roles cannot be deleted.')
    {
        parent::__construct($message);
    }

    public function errorCode(): ErrorCode
    {
        return ErrorCode::SystemRoleImmutable;
    }

    public function errorMeta(): array
    {
        return [];
    }
}
