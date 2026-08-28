<?php

declare(strict_types=1);

namespace App\Domains\Reporting\Exceptions;

use App\Support\Http\Errors\ErrorCode;
use App\Support\Http\Errors\HasApiErrorCode;

class UnknownReportException extends \DomainException implements HasApiErrorCode
{
    public function __construct(
        public string $reportKey,
        string $message = '',
    ) {
        parent::__construct($message ?: "Unknown report: {$reportKey}");
    }

    public static function for(string $reportKey): self
    {
        return new self($reportKey);
    }

    public function errorCode(): ErrorCode
    {
        return ErrorCode::UnknownReport;
    }

    public function errorMeta(): array
    {
        return [];
    }
}
