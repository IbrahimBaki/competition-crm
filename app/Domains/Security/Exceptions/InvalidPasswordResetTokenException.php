<?php

namespace App\Domains\Security\Exceptions;

use App\Support\Http\Errors\ErrorCode;
use App\Support\Http\Errors\HasApiErrorCode;

class InvalidPasswordResetTokenException extends \DomainException implements HasApiErrorCode
{
    public function __construct()
    {
        parent::__construct(__('errors.security.password_reset_token_invalid'));
    }

    public function errorCode(): ErrorCode
    {
        return ErrorCode::PasswordResetTokenInvalid;
    }

    public function errorMeta(): array
    {
        return [];
    }
}
