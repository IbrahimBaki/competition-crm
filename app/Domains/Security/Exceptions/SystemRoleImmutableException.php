<?php

namespace App\Domains\Security\Exceptions;

use App\Support\Http\Errors\ErrorCode;
use App\Support\Http\Errors\HasApiErrorCode;
use Exception;

class SystemRoleImmutableException extends Exception implements HasApiErrorCode
{
    public function __construct(?string $message = null)
    {
        parent::__construct($message ?? __('errors.security.system_role_immutable'));
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
