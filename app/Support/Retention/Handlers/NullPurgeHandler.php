<?php

namespace App\Support\Retention\Handlers;

use App\Support\Retention\PurgeHandler;
use App\Support\Retention\RetentionPolicy;

class NullPurgeHandler implements PurgeHandler
{
    public function __construct(private string $dataClass) {}

    public function dataClass(): string
    {
        return $this->dataClass;
    }

    public function purge(RetentionPolicy $policy): int
    {
        return 0;
    }
}
