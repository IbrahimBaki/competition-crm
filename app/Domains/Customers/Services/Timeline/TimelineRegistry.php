<?php

namespace App\Domains\Customers\Services\Timeline;

class TimelineRegistry
{
    private array $sources = [];

    public function register(TimelineSource $source): void
    {
        $this->sources[$source->key()] = $source;
    }

    public function sources(): array
    {
        return $this->sources;
    }
}
