<?php

namespace App\Support\Attachments\Exceptions;

use App\Support\Http\Errors\ErrorCode;
use App\Support\Http\Errors\HasApiErrorCode;
use Exception;

class AttachmentInfectedException extends Exception implements HasApiErrorCode
{
    public function __construct()
    {
        parent::__construct(__('errors.attachments.scan_failed'));
    }

    public function errorCode(): ErrorCode
    {
        return ErrorCode::AttachmentScanFailed;
    }

    /**
     * @return array<string, mixed>
     */
    public function errorMeta(): array
    {
        return [];
    }
}
