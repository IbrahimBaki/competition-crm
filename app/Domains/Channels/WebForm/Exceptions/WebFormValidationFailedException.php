<?php

namespace App\Domains\Channels\WebForm\Exceptions;

use App\Support\Http\Errors\ErrorCode;
use App\Support\Http\Errors\HasApiErrorCode;
use Exception;

class WebFormValidationFailedException extends Exception implements HasApiErrorCode
{
    /**
     * @param  array<string, array<int, string>>  $errors
     */
    public function __construct(
        private readonly array $errors
    ) {
        parent::__construct(__('errors.channels.web_form.validation_failed'));
    }

    public function errorCode(): ErrorCode
    {
        return ErrorCode::WebFormValidationFailed;
    }

    /**
     * @return array<string, mixed>
     */
    public function errorMeta(): array
    {
        return ['fields' => $this->errors];
    }
}
