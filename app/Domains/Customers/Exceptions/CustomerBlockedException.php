<?php

namespace App\Domains\Customers\Exceptions;

use App\Support\Http\Errors\ErrorCode;
use App\Support\Http\Errors\HasApiErrorCode;
use Exception;

class CustomerBlockedException extends Exception implements HasApiErrorCode
{
    public function __construct(private string $reason)
    {
        parent::__construct("Customer is blocked: {$reason}");
    }

    public function errorCode(): ErrorCode
    {
        return ErrorCode::CustomerBlocked;
    }

    public function errorMeta(): array
    {
        return ['reason' => $this->reason];
    }
}
