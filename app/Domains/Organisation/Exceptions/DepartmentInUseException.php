<?php

namespace App\Domains\Organisation\Exceptions;

use Exception;

class DepartmentInUseException extends Exception
{
    public function __construct(public readonly int $openTicketCount)
    {
        parent::__construct("Department has {$openTicketCount} open tickets", 409);
    }
}
