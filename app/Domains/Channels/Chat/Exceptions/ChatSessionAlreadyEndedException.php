<?php

namespace App\Domains\Channels\Chat\Exceptions;

use App\Support\Http\Errors\ErrorCode;
use App\Support\Http\Errors\HasApiErrorCode;
use Exception;

class ChatSessionAlreadyEndedException extends Exception implements HasApiErrorCode
{
    public function __construct(string $message = '')
    {
        parent::__construct($message ?: __('errors.channels.chat.session_already_ended'));
    }

    public function errorCode(): ErrorCode
    {
        return ErrorCode::ChatSessionAlreadyEnded;
    }

    public function errorMeta(): array
    {
        return [];
    }
}
