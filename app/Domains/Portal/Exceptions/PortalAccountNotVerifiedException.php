<?php

namespace App\Domains\Portal\Exceptions;

use App\Support\Http\Errors\ErrorCode;
use App\Support\Http\Errors\HasApiErrorCode;
use Exception;

class PortalAccountNotVerifiedException extends Exception implements HasApiErrorCode
{
    public function __construct()
    {
        parent::__construct(__('errors.portal.account_not_verified'));
    }

    public function errorCode(): ErrorCode
    {
        return ErrorCode::PortalAccountNotVerified;
    }

    /**
     * @return array<string, mixed>
     */
    public function errorMeta(): array
    {
        return [];
    }
}
