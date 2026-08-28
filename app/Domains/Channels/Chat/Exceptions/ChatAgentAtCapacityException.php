<?php

namespace App\Domains\Channels\Chat\Exceptions;

use App\Support\Http\Errors\ErrorCode;
use App\Support\Http\Errors\HasApiErrorCode;
use Exception;

class ChatAgentAtCapacityException extends Exception implements HasApiErrorCode
{
    public function __construct(string $message = '')
    {
        parent::__construct($message ?: __('errors.channels.chat.agent_at_capacity'));
    }

    public function errorCode(): ErrorCode
    {
        return ErrorCode::ChatAgentAtCapacity;
    }

    public function errorMeta(): array
    {
        return [];
    }
}
