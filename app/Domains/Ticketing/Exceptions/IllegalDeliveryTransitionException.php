<?php

namespace App\Domains\Ticketing\Exceptions;

use App\Support\Http\Errors\ErrorCode;
use App\Support\Http\Errors\HasApiErrorCode;
use Exception;

class IllegalDeliveryTransitionException extends Exception implements HasApiErrorCode
{
    public function errorCode(): ErrorCode
    {
        return ErrorCode::IllegalDeliveryTransition;
    }

    public function errorMeta(): array
    {
        return [];
    }
}
