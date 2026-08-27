<?php

namespace App\Domains\Integrations\Exceptions;

use App\Support\Http\Errors\ErrorCode;
use App\Support\Http\Errors\HasApiErrorCode;
use Exception;

final class ImportValidationFailedException extends Exception implements HasApiErrorCode
{
    public function errorCode(): ErrorCode
    {
        return ErrorCode::ImportValidationFailed;
    }

    public function errorMeta(): array
    {
        return [];
    }
}
