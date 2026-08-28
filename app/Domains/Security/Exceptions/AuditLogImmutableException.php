<?php

namespace App\Domains\Security\Exceptions;

use App\Support\Http\Errors\ErrorCode;
use App\Support\Http\Errors\HasApiErrorCode;
use RuntimeException;

final class AuditLogImmutableException extends RuntimeException implements HasApiErrorCode
{
    public function __construct()
    {
        parent::__construct('Audit log entries are immutable.');
    }

    public function errorCode(): ErrorCode
    {
        return ErrorCode::AuditLogImmutable;
    }

    public function errorMeta(): array
    {
        return [];
    }
}
