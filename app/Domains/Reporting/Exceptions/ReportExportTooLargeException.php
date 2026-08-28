<?php

declare(strict_types=1);

namespace App\Domains\Reporting\Exceptions;

use App\Support\Http\Errors\ErrorCode;
use App\Support\Http\Errors\HasApiErrorCode;

class ReportExportTooLargeException extends \DomainException implements HasApiErrorCode
{
    public function __construct(
        public int $rowCount,
        public int $maxRows,
        string $message = '',
    ) {
        parent::__construct($message ?: "Export would have {$rowCount} rows, exceeding limit of {$maxRows}");
    }

    public static function for(int $rowCount, int $maxRows): self
    {
        return new self($rowCount, $maxRows);
    }

    public function errorCode(): ErrorCode
    {
        return ErrorCode::ReportExportTooLarge;
    }

    public function errorMeta(): array
    {
        return [];
    }
}
