<?php

namespace App\Support\Http\Exceptions;

use App\Support\Http\Errors\ErrorCode;
use App\Support\Http\Errors\HasApiErrorCode;
use Exception;

class BotProtectionFailedException extends Exception implements HasApiErrorCode
{
    public function __construct()
    {
        parent::__construct(__('errors.security.bot_protection_failed'));
    }

    public function errorCode(): ErrorCode
    {
        return ErrorCode::BotProtectionFailed;
    }

    /**
     * @return array<string, mixed>
     */
    public function errorMeta(): array
    {
        return [];
    }
}
