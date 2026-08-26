<?php

namespace App\Domains\Security\Exceptions;

use Exception;

class SystemRoleImmutableException extends Exception
{
    public function __construct(string $message = 'System roles cannot be deleted.')
    {
        parent::__construct($message);
    }
}
