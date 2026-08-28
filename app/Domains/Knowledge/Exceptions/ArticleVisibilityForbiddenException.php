<?php

namespace App\Domains\Knowledge\Exceptions;

use App\Support\Http\Errors\ErrorCode;
use App\Support\Http\Errors\HasApiErrorCode;
use Exception;

class ArticleVisibilityForbiddenException extends Exception implements HasApiErrorCode
{
    public function __construct(string $message = '')
    {
        parent::__construct($message ?: __('errors.knowledge.article_visibility_forbidden'));
    }

    public function errorCode(): ErrorCode
    {
        return ErrorCode::ArticleVisibilityForbidden;
    }

    public function errorMeta(): array
    {
        return [];
    }
}
