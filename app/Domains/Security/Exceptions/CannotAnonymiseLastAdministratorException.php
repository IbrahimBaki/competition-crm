<?php

namespace App\Domains\Security\Exceptions;

use App\Support\Http\Errors\ErrorCode;
use App\Support\Http\Errors\HasApiErrorCode;

class CannotAnonymiseLastAdministratorException extends \Exception implements HasApiErrorCode
{
    public function errorCode(): ErrorCode
    {
        return ErrorCode::CannotAnonymiseLastAdmin;
    }

    public function errorMeta(): array
    {
        return [];
    }
}
