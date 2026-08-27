<?php

namespace App\Domains\Knowledge\Exceptions;

use App\Support\Http\Errors\HasApiErrorCode;
use Exception;

class IllegalArticleTransitionException extends Exception implements HasApiErrorCode
{
    public function apiErrorCode(): string
    {
        return 'illegal_article_transition';
    }
}
