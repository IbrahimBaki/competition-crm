<?php

namespace App\Domains\Organisation\Exceptions;

use App\Support\Http\Errors\ErrorCode;
use App\Support\Http\Errors\HasApiErrorCode;
use Exception;

class BranchHasActiveDepartmentsException extends Exception implements HasApiErrorCode
{
    public function __construct(public readonly int $activeDepartmentCount)
    {
        parent::__construct(__('errors.organisation.branch_has_active_departments', ['count' => $activeDepartmentCount]), 409);
    }

    public function errorCode(): ErrorCode
    {
        return ErrorCode::BranchHasActiveDepartments;
    }

    public function errorMeta(): array
    {
        return [
            'active_departments' => $this->activeDepartmentCount,
        ];
    }
}
