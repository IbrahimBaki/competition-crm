<?php

declare(strict_types=1);

namespace App\Domains\Reporting\Exceptions;

use App\Support\Http\Errors\ErrorCode;
use App\Support\Http\Errors\HasApiErrorCode;

class ReportExportNotReadyException extends \DomainException implements HasApiErrorCode
{
    public function __construct(string $message = '')
    {
        parent::__construct($message ?: 'Export is not yet ready for download');
    }

    public function errorCode(): ErrorCode
    
    public function errorMeta(): array
    {
        return [];
    }
    {
        return ErrorCode::ReportExportNotReady;
    }
}
