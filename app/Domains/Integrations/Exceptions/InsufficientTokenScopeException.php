<?php

namespace App\Domains\Integrations\Exceptions;

use App\Support\Http\Errors\ErrorCode;
use App\Support\Http\Errors\HasApiErrorCode;
use Exception;

final class InsufficientTokenScopeException extends Exception implements HasApiErrorCode
{
    public function __construct(private readonly string $missingScope)
    {
        parent::__construct("Missing required scope: {$missingScope}");
    }

    public function errorCode(): ErrorCode
    {
        return ErrorCode::InsufficientTokenScope;
    }

    public function errorMeta(): array
    {
        return ['missing_scope' => $this->missingScope];
    }
}
