<?php

namespace App\Domains\Portal\Exceptions;

use App\Support\Http\Errors\ErrorCode;
use App\Support\Http\Errors\HasApiErrorCode;
use Exception;

class PortalVerificationTokenInvalidException extends Exception implements HasApiErrorCode
{
    public function __construct()
    {
        parent::__construct(__('errors.portal.verification_token_invalid'));
    }

    public function errorCode(): ErrorCode
    {
        return ErrorCode::PortalVerificationTokenInvalid;
    }

    /**
     * @return array<string, mixed>
     */
    public function errorMeta(): array
    {
        return [];
    }
}
