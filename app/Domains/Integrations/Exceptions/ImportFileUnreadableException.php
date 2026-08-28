<?php

namespace App\Domains\Integrations\Exceptions;

use App\Support\Http\Errors\ErrorCode;
use App\Support\Http\Errors\HasApiErrorCode;
use Exception;

final class ImportFileUnreadableException extends Exception implements HasApiErrorCode
{
    public function errorCode(): ErrorCode
    {
        return ErrorCode::ImportFileUnreadable;
    }

    public function errorMeta(): array
    {
        return [];
    }
}
