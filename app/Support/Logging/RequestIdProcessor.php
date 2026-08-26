<?php

namespace App\Support\Logging;

use App\Support\Http\RequestId;
use Monolog\LogRecord;

final class RequestIdProcessor
{
    public function __invoke(LogRecord $record): LogRecord
    {
        return $record->with(extra: $record->extra + ['request_id' => RequestId::current()]);
    }
}
