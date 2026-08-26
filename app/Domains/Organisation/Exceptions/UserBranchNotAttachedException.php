<?php

namespace App\Domains\Organisation\Exceptions;

use Exception;

class UserBranchNotAttachedException extends Exception
{
    public function __construct()
    {
        parent::__construct('User is not attached to this branch', 422);
    }
}
