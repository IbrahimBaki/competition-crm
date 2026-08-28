<?php

namespace App\Support\Retention;

class RetentionRegistry
{
    /** @var array<string, PurgeHandler> */
    private array $handlers = [];

    public function register(PurgeHandler $handler): void
    {
        $this->handlers[$handler->dataClass()] = $handler;
    }

    public function get(string $dataClass): PurgeHandler
    {
        if (! isset($this->handlers[$dataClass])) {
            throw new \RuntimeException("No purge handler registered for data class: {$dataClass}");
        }

        return $this->handlers[$dataClass];
    }

    public function has(string $dataClass): bool
    {
        return isset($this->handlers[$dataClass]);
    }

    public function all(): array
    {
        return $this->handlers;
    }
}
