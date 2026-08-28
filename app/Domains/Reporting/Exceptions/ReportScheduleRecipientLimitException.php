<?php

declare(strict_types=1);

namespace App\Domains\Reporting\Exceptions;

use App\Support\Http\Errors\ErrorCode;
use App\Support\Http\Errors\HasApiErrorCode;

class ReportScheduleRecipientLimitException extends \DomainException implements HasApiErrorCode
{
    public function __construct(
        public int $count,
        public int $maxRecipients,
        string $message = '',
    ) {
        parent::__construct($message ?: "Too many recipients: {$count} exceeds limit of {$maxRecipients}");
    }

    public static function for(int $count, int $maxRecipients): self
    {
        return new self($count, $maxRecipients);
    }

    public function errorCode(): ErrorCode
    {
        return ErrorCode::ReportScheduleRecipientLimitExceeded;
    }

    public function errorMeta(): array
    {
        return [];
    }
}
