<?php

namespace App\Domains\Channels\Messaging\Exceptions;

use App\Support\Http\Errors\ErrorCode;
use App\Support\Http\Errors\HasApiErrorCode;
use Exception;

class ProviderTemplateNotApprovedException extends Exception implements HasApiErrorCode
{
    public function errorCode(): ErrorCode
    {
        return ErrorCode::ProviderTemplateNotApproved;
    }

    public function getPayload(): array
    {
        return [];
    }
}
