<?php

namespace App\Domains\Knowledge\Exceptions;

use App\Support\Http\Errors\ErrorCode;
use App\Support\Http\Errors\HasApiErrorCode;
use Exception;

class ArticleVersionNotFoundException extends Exception implements HasApiErrorCode
{
    public function __construct(string $message = '')
    {
        parent::__construct($message ?: __('errors.knowledge.article_version_not_found'));
    }

    public function errorCode(): ErrorCode
    {
        return ErrorCode::ArticleVersionNotFound;
    }

    public function errorMeta(): array
    {
        return [];
    }
}
