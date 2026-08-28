<?php

namespace App\Domains\Ai\Exceptions;

use App\Support\Http\Errors\ErrorCode;
use App\Support\Http\Errors\HasApiErrorCode;
use Exception;

class AiProviderUnavailableException extends Exception implements HasApiErrorCode
{
    public function __construct(
        private readonly string $reason = '',
        private readonly string $detail = '',
    ) {
        parent::__construct("AI provider is unavailable: {$reason}");
    }

    public function errorCode(): ErrorCode
    {
        return ErrorCode::AiProviderUnavailable;
    }

    public function errorMeta(): array
    {
        return [
            'reason' => $this->reason,
            'detail' => $this->detail,
        ];
    }
}
