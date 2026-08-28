<?php

namespace App\Domains\Knowledge\Exceptions;

use App\Support\Http\Errors\ErrorCode;
use App\Support\Http\Errors\HasApiErrorCode;
use Exception;

class ArticleNotPublishedException extends Exception implements HasApiErrorCode
{
    public function __construct(string $message = '')
    {
        parent::__construct($message ?: __('errors.knowledge.article_not_published'));
    }

    public function errorCode(): ErrorCode
    {
        return ErrorCode::ArticleNotPublished;
    }

    public function errorMeta(): array
    {
        return [];
    }
}
