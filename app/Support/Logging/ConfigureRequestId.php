<?php

namespace App\Support\Logging;

final class ConfigureRequestId
{
    public function __invoke($logger): void
    {
        foreach ($logger->getHandlers() as $handler) {
            $handler->pushProcessor(new RequestIdProcessor);
            $handler->pushProcessor(new RedactSensitiveProcessor);
        }
    }
}
