<?php

namespace App\Domains\Customers\Exceptions;

use App\Support\Http\Errors\ErrorCode;
use App\Support\Http\Errors\HasApiErrorCode;
use Exception;

class CustomerMustHaveContactException extends Exception implements HasApiErrorCode
{
    public function errorCode(): ErrorCode
    {
        return ErrorCode::CustomerMustHaveContact;
    }

    public function errorMeta(): array
    {
        return [];
    }
}
