<?php

namespace App\Domains\Channels\Messaging\Exceptions;

use App\Support\Http\Errors\ErrorCode;
use App\Support\Http\Errors\HasApiErrorCode;
use Exception;

class ProviderMessageSendFailedException extends Exception implements HasApiErrorCode
{
    public function __construct(
        private readonly string $reason,
        private readonly string $detail = '',
    ) {
        parent::__construct("Provider message send failed: {$reason}");
    }

    public function errorCode(): ErrorCode
    {
        return ErrorCode::ProviderMessageSendFailed;
    }

    public function getPayload(): array
    {
        return [
            'reason' => $this->reason,
            'detail' => $this->detail,
        ];
    }
}
