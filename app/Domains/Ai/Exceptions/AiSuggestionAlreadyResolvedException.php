<?php

namespace App\Domains\Ai\Exceptions;

use App\Support\Http\Errors\ErrorCode;
use App\Support\Http\Errors\HasApiErrorCode;
use Exception;

class AiSuggestionAlreadyResolvedException extends Exception implements HasApiErrorCode
{
    public function errorCode(): ErrorCode
    {
        return ErrorCode::AiSuggestionAlreadyResolved;
    }

    public function errorMeta(): array
    {
        return [];
    }
}
