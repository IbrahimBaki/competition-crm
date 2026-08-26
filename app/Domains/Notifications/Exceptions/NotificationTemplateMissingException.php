<?php

namespace App\Domains\Notifications\Exceptions;

use App\Support\Http\Errors\ErrorCode;
use App\Support\Http\Errors\HasApiErrorCode;
use Exception;

class NotificationTemplateMissingException extends Exception implements HasApiErrorCode
{
    public function __construct(
        string $code,
        string $channel,
    ) {
        parent::__construct("Notification template not found: {$code} for channel {$channel}");
    }

    public function errorCode(): ErrorCode
    {
        return ErrorCode::NotificationTemplateMissing;
    }

    public function errorMeta(): array
    {
        return [];
    }
}
