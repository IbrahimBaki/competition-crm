<?php

namespace App\Support\Http;

final class RequestId
{
    private static ?string $id = null;

    public static function set(string $id): void
    {
        self::$id = $id;
    }

    public static function current(): string
    {
        return self::$id ?? '';
    }
}
