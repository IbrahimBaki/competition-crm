<?php

namespace App\Domains\Integrations\Actions;

final class IssuedApiTokenDTO
{
    public function __construct(
        public readonly string $uuid,
        public readonly string $plaintext,
        public readonly string $name,
        public readonly array $scopes,
    ) {}
}
