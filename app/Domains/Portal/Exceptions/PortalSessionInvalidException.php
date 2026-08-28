<?php

namespace App\Domains\Portal\Exceptions;

use App\Support\Http\Errors\ErrorCode;
use App\Support\Http\Errors\HasApiErrorCode;
use Exception;

class PortalSessionInvalidException extends Exception implements HasApiErrorCode
{
    public function __construct()
    {
        parent::__construct(__('errors.portal.session_invalid'));
    }

    public function errorCode(): ErrorCode
    {
        return ErrorCode::PortalSessionInvalid;
    }

    /**
     * @return array<string, mixed>
     */
    public function errorMeta(): array
    {
        return [];
    }
}
