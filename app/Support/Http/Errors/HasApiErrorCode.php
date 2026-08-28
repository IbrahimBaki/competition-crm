<?php

namespace App\Support\Http\Errors;

interface HasApiErrorCode
{
    public function errorCode(): ErrorCode;

    public function errorMeta(): array;
}
