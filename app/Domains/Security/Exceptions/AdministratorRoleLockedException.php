<?php

namespace App\Domains\Security\Exceptions;

use Exception;

class AdministratorRoleLockedException extends Exception
{
    public function __construct(string $message = 'Cannot remove administrative permissions from the administrator role.')
    {
        parent::__construct($message);
    }
}
