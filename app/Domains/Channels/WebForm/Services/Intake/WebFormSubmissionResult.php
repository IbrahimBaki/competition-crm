<?php

namespace App\Domains\Channels\WebForm\Services\Intake;

readonly class WebFormSubmissionResult
{
    public function __construct(
        public string $ticketReference,
        public string $trackingToken,
        public bool $wasDuplicate = false,
    ) {}
}
