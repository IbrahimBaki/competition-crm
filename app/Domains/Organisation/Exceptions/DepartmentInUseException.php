<?php

namespace App\Domains\Organisation\Exceptions;

use App\Support\Http\Errors\ErrorCode;
use App\Support\Http\Errors\HasApiErrorCode;
use Exception;

class DepartmentInUseException extends Exception implements HasApiErrorCode
{
    public function __construct(public readonly int $openTicketCount)
    {
        parent::__construct("Department has {$openTicketCount} open tickets", 409);
    }

    public function errorCode(): ErrorCode
    {
        return ErrorCode::DepartmentHasOpenTickets;
    }

    public function errorMeta(): array
    {
        return [
            'open_tickets' => $this->openTicketCount,
        ];
    }
}
