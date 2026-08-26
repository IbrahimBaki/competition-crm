<?php

namespace App\Domains\Security\Exceptions;

use App\Support\Errors\Contracts\HasApiErrorCode;
use App\Support\Errors\ErrorCode;

class CannotDeactivateLastAdministratorException extends \DomainException implements HasApiErrorCode
{
    public function __construct()
    {
        parent::__construct(__('errors.security.cannot_deactivate_last_administrator'));
    }

    public function errorCode(): ErrorCode
    {
        return ErrorCode::CannotDeactivateLastAdmin;
    }

    public function errorMeta(): array
    {
        return [];
    }
}
