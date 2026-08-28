<?php

namespace App\Domains\Ticketing\Exceptions;

use App\Support\Http\Errors\ErrorCode;
use App\Support\Http\Errors\HasApiErrorCode;
use Exception;

class CannotLinkTicketToItselfException extends Exception implements HasApiErrorCode
{
    public function errorCode(): ErrorCode
    {
        return ErrorCode::TicketCannotLinkToItself;
    }

    public function errorMeta(): array
    {
        return [];
    }
}
