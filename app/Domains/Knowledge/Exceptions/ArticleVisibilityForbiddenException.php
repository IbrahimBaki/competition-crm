<?php

namespace App\Domains\Knowledge\Exceptions;

use App\Support\Http\Errors\HasApiErrorCode;
use Exception;

class ArticleVisibilityForbiddenException extends Exception implements HasApiErrorCode
{
    public function apiErrorCode(): string
    {
        return 'article_visibility_forbidden';
    }
}
