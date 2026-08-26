<?php

namespace App\Support\Attachments\Exceptions;

use App\Support\Http\Errors\ErrorCode;
use App\Support\Http\Errors\HasApiErrorCode;
use Exception;

class AttachmentPendingScanException extends Exception implements HasApiErrorCode
{
    public function __construct()
    {
        parent::__construct(__('errors.attachments.scan_pending'));
    }

    public function errorCode(): ErrorCode
    {
        return ErrorCode::AttachmentScanPending;
    }

    /**
     * @return array<string, mixed>
     */
    public function errorMeta(): array
    {
        return [];
    }
}
