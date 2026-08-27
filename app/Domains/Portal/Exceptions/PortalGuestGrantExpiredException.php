<?php

namespace App\Domains\Portal\Exceptions;

use App\Support\Http\Errors\ErrorCode;
use App\Support\Http\Errors\HasApiErrorCode;
use Exception;

class PortalGuestGrantExpiredException extends Exception implements HasApiErrorCode
{
    public function __construct()
    {
        parent::__construct(__('errors.portal.guest_grant_expired'));
    }

    public function errorCode(): ErrorCode
    {
        return ErrorCode::PortalGuestGrantExpired;
    }

    /**
     * @return array<string, mixed>
     */
    public function errorMeta(): array
    {
        return [];
    }
}
