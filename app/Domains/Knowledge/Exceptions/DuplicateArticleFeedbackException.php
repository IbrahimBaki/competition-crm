<?php

namespace App\Domains\Knowledge\Exceptions;

use App\Support\Http\Errors\HasApiErrorCode;
use Exception;

class DuplicateArticleFeedbackException extends Exception implements HasApiErrorCode
{
    public function apiErrorCode(): string
    {
        return 'duplicate_article_feedback';
    }
}
