<?php

namespace App\Domains\Channels\WebForm\Exceptions;

use App\Support\Http\Errors\ErrorCode;
use App\Support\Http\Errors\HasApiErrorCode;
use Exception;

class WebFormInactiveException extends Exception implements HasApiErrorCode
{
    public function __construct()
    {
        parent::__construct(__('errors.channels.web_form.inactive'));
    }

    public function errorCode(): ErrorCode
    {
        return ErrorCode::WebFormInactive;
    }

    /**
     * @return array<string, mixed>
     */
    public function errorMeta(): array
    {
        return [];
    }
}
