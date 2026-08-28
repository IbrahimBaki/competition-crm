<?php

namespace App\Domains\Knowledge\Exceptions;

use App\Support\Http\Errors\ErrorCode;
use App\Support\Http\Errors\HasApiErrorCode;
use Exception;

class DuplicateArticleFeedbackException extends Exception implements HasApiErrorCode
{
    public function __construct(string $message = '')
    {
        parent::__construct($message ?: __('errors.knowledge.duplicate_article_feedback'));
    }

    public function errorCode(): ErrorCode
    {
        return ErrorCode::DuplicateArticleFeedback;
    }

    public function errorMeta(): array
    {
        return [];
    }
}
