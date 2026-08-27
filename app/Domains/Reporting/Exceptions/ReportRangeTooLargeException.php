<?php

declare(strict_types=1);

namespace App\Domains\Reporting\Exceptions;

use App\Support\Http\Errors\ErrorCode;
use App\Support\Http\Errors\HasApiErrorCode;

class ReportRangeTooLargeException extends \DomainException implements HasApiErrorCode
{
    public function __construct(
        public int $maxDays,
        string $message = '',
    ) {
        parent::__construct($message ?: "Report range exceeds maximum of {$maxDays} days");
    }

    public static function for(int $maxDays): self
    {
        return new self($maxDays);
    }

    public function getErrorCode(): ErrorCode
    {
        return ErrorCode::ReportRangeTooLarge;
    }
}
