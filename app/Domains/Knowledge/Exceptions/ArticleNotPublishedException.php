<?php

namespace App\Domains\Knowledge\Exceptions;

use App\Support\Http\Errors\HasApiErrorCode;
use Exception;

class ArticleNotPublishedException extends Exception implements HasApiErrorCode
{
    public function apiErrorCode(): string
    {
        return 'article_not_published';
    }
}
