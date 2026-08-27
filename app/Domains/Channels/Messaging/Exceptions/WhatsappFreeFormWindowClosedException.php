<?php

namespace App\Domains\Channels\Messaging\Exceptions;

use App\Support\Http\Errors\ErrorCode;
use App\Support\Http\Errors\HasApiErrorCode;
use Carbon\CarbonInterface;
use Exception;

class WhatsappFreeFormWindowClosedException extends Exception implements HasApiErrorCode
{
    public function __construct(
        private readonly CarbonInterface $windowExpiresAt,
    ) {
        parent::__construct('WhatsApp free-form reply window is closed');
    }

    public function errorCode(): ErrorCode
    {
        return ErrorCode::WhatsappFreeFormWindowClosed;
    }

    public function getPayload(): array
    {
        return [
            'window_expired_at' => $this->windowExpiresAt->toIso8601String(),
            'template_required' => true,
        ];
    }
}
