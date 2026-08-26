<?php

namespace App\Support\Attachments\Exceptions;

use App\Support\Http\Errors\ErrorCode;
use App\Support\Http\Errors\HasApiErrorCode;
use Exception;

class AttachmentTooLargeException extends Exception implements HasApiErrorCode
{
    public function __construct(int $maxKb)
    {
        parent::__construct(__('errors.attachments.too_large', ['max' => $maxKb]));
    }

    public function errorCode(): ErrorCode
    {
        return ErrorCode::AttachmentTooLarge;
    }

    /**
     * @return array<string, mixed>
     */
    public function errorMeta(): array
    {
        return [];
    }
}
