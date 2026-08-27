<?php

declare(strict_types=1);

namespace App\Domains\Reporting\Exceptions;

use App\Support\Http\Errors\ErrorCode;
use App\Support\Http\Errors\HasApiErrorCode;

class UnsupportedExportFormatException extends \DomainException implements HasApiErrorCode
{
    public function __construct(
        public string $format,
        string $message = '',
    ) {
        parent::__construct($message ?: "Unsupported export format: {$format}");
    }

    public static function for(string $format): self
    {
        return new self($format);
    }

    public function errorCode(): ErrorCode
    
    public function errorMeta(): array
    {
        return [];
    }
    {
        return ErrorCode::UnsupportedExportFormat;
    }
}
