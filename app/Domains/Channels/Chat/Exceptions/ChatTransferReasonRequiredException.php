<?php

namespace App\Domains\Channels\Chat\Exceptions;

use App\Support\Http\Errors\ErrorCode;
use App\Support\Http\Errors\HasApiErrorCode;
use Exception;

class ChatTransferReasonRequiredException extends Exception implements HasApiErrorCode
{
    public function __construct(string $message = '')
    {
        parent::__construct($message ?: __('errors.channels.chat.transfer_reason_required'));
    }

    public function errorCode(): ErrorCode
    {
        return ErrorCode::ChatTransferReasonRequired;
    }

    public function errorMeta(): array
    {
        return [];
    }
}
