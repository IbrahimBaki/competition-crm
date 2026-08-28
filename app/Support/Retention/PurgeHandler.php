<?php

namespace App\Support\Retention;

interface PurgeHandler
{
    public function dataClass(): string;

    public function purge(RetentionPolicy $policy): int;
}
