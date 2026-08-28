<?php

namespace App\Support\Attachments\Scanning;

use App\Support\Attachments\ScanState;

readonly class ScanResult
{
    public function __construct(
        public ScanState $state,
        public ?string $reason = null,
    ) {}
}
