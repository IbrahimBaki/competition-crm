<?php

namespace App\Domains\Integrations\Exceptions;

use App\Support\Http\Errors\ErrorCode;
use App\Support\Http\Errors\HasApiErrorCode;
use Exception;

final class IntegrationDependencyUnavailableException extends Exception implements HasApiErrorCode
{
    public function errorCode(): ErrorCode
    {
        return ErrorCode::IntegrationDependencyUnavailable;
    }

    public function errorMeta(): array
    {
        return [];
    }
}
