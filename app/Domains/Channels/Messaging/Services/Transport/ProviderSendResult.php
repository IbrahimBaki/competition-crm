<?php

namespace App\Domains\Channels\Messaging\Services\Transport;

final class ProviderSendResult
{
    public function __construct(
        public readonly string $providerMessageId,
        public readonly bool $success = true,
        public readonly ?string $failureReason = null,
        public readonly ?string $failureDetail = null,
    ) {}

    public static function success(string $providerMessageId): self
    {
        return new self(
            providerMessageId: $providerMessageId,
            success: true,
        );
    }

    public static function failed(
        string $providerMessageId,
        string $reason,
        string $detail = '',
    ): self {
        return new self(
            providerMessageId: $providerMessageId,
            success: false,
            failureReason: $reason,
            failureDetail: $detail,
        );
    }
}
