<?php

namespace App\Domains\Ticketing\Exceptions;

use App\Support\Http\Errors\ErrorCode;
use App\Support\Http\Errors\HasApiErrorCode;
use Exception;

class MentionNotAllowedOnPublicReplyException extends Exception implements HasApiErrorCode
{
    public function errorCode(): ErrorCode
    {
        return ErrorCode::MentionNotAllowedOnPublicReply;
    }

    public function errorMeta(): array
    {
        return [];
    }
}
