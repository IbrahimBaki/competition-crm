<?php

namespace App\Domains\Ticketing\Exceptions;

use App\Support\Http\Errors\ErrorCode;
use App\Support\Http\Errors\HasApiErrorCode;
use Exception;

class InvalidTicketCustomFieldException extends Exception implements HasApiErrorCode
{
    public function __construct(
        private readonly string $field,
        private readonly string $reason,
    ) {
        parent::__construct("Invalid custom field: {$field} ({$reason})");
    }

    public static function missingRequired(string $field): self
    {
        return new self($field, 'missing_required');
    }

    public static function typeMismatch(string $field): self
    {
        return new self($field, 'type_mismatch');
    }

    public static function invalidSelectValue(string $field): self
    {
        return new self($field, 'invalid_select_value');
    }

    public static function unknownField(string $field): self
    {
        return new self($field, 'unknown_field');
    }

    public function errorCode(): ErrorCode
    {
        return ErrorCode::InvalidTicketCustomField;
    }

    public function errorMeta(): array
    {
        return ['field' => $this->field];
    }
}
