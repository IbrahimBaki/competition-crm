<?php

namespace App\Domains\Knowledge\Exceptions;

use App\Support\Http\Errors\HasApiErrorCode;
use Exception;

class KnowledgeCategoryDepthExceededException extends Exception implements HasApiErrorCode
{
    public function apiErrorCode(): string
    {
        return 'knowledge_category_depth_exceeded';
    }
}
