<?php

namespace App\Domains\Knowledge\Exceptions;

use App\Support\Http\Errors\ErrorCode;
use App\Support\Http\Errors\HasApiErrorCode;
use Exception;

class IllegalArticleTransitionException extends Exception implements HasApiErrorCode
{
    public function __construct(string $message = '')
    {
        parent::__construct($message ?: __('errors.knowledge.illegal_article_transition'));
    }

    public function errorCode(): ErrorCode
    {
        return ErrorCode::IllegalArticleTransition;
    }

    public function errorMeta(): array
    {
        return [];
    }
}
