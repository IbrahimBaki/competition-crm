<?php

namespace App\Domains\Security\Exceptions;

use App\Support\Http\Errors\ErrorCode;
use App\Support\Http\Errors\HasApiErrorCode;
use Exception;

class AdministratorRoleLockedException extends Exception implements HasApiErrorCode
{
    public function __construct(?string $message = null)
    {
        parent::__construct($message ?? __('errors.security.administrator_role_locked'));
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
