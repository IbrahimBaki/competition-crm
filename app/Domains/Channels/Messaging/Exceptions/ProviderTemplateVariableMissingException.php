<?php

namespace App\Domains\Channels\Messaging\Exceptions;

use App\Support\Http\Errors\ErrorCode;
use App\Support\Http\Errors\HasApiErrorCode;
use Exception;

class ProviderTemplateVariableMissingException extends Exception implements HasApiErrorCode
{
    public function __construct(
        private readonly string $variableName,
    ) {
        parent::__construct("Template variable '{$variableName}' is missing");
    }

    public function errorCode(): ErrorCode
    {
        return ErrorCode::ProviderTemplateVariableMissing;
    }

    public function errorMeta(): array
    {
        return [
            'variable_name' => $this->variableName,
        ];
    }
}
