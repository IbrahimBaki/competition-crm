<?php

declare(strict_types=1);

namespace App\Domains\Reporting\Exceptions;

use App\Support\Http\Errors\ErrorCode;
use App\Support\Http\Errors\HasApiErrorCode;

class ReportExportExpiredException extends \DomainException implements HasApiErrorCode
{
    public function __construct(string $message = '')
    {
        parent::__construct($message ?: 'Export has expired and is no longer available');
    }

    public function getErrorCode(): ErrorCode
    {
        return ErrorCode::ReportExportExpired;
    }
}
