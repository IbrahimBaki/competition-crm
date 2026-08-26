<?php

namespace App\Domains\Organisation\Exceptions;

use Exception;

class BranchHasActiveDepartmentsException extends Exception
{
    public function __construct(public readonly int $activeDepartmentCount)
    {
        parent::__construct("Branch has {$activeDepartmentCount} active departments", 409);
    }
}
