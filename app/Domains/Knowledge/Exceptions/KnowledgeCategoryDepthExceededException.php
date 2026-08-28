<?php

namespace App\Domains\Knowledge\Exceptions;

use App\Support\Http\Errors\ErrorCode;
use App\Support\Http\Errors\HasApiErrorCode;
use Exception;

class KnowledgeCategoryDepthExceededException extends Exception implements HasApiErrorCode
{
    public function __construct(string $message = '')
    {
        parent::__construct($message ?: __('errors.knowledge.category_depth_exceeded'));
    }

    public function errorCode(): ErrorCode
    {
        return ErrorCode::KnowledgeCategoryDepthExceeded;
    }

    public function errorMeta(): array
    {
        return [];
    }
}
