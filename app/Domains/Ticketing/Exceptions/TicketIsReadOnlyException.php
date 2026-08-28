<?php

namespace App\Domains\Ticketing\Exceptions;

use App\Support\Http\Errors\ErrorCode;
use App\Support\Http\Errors\HasApiErrorCode;
use Exception;

class TicketIsReadOnlyException extends Exception implements HasApiErrorCode
{
    public function errorCode(): ErrorCode
    {
        return ErrorCode::TicketIsReadOnly;
    }

    public function errorMeta(): array
    {
        return [];
    }
}
