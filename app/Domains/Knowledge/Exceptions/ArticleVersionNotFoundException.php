<?php

namespace App\Domains\Knowledge\Exceptions;

use App\Support\Http\Errors\HasApiErrorCode;
use Exception;

class ArticleVersionNotFoundException extends Exception implements HasApiErrorCode
{
    public function apiErrorCode(): string
    {
        return 'article_version_not_found';
    }
}
