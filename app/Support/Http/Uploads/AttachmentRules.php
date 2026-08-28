<?php

namespace App\Support\Http\Uploads;

final class AttachmentRules
{
    /**
     * @return array<int, mixed>
     */
    public static function file(): array
    {
        return [
            'file',
            'max:'.self::maxSizeKb(),
            'mimetypes:'.implode(',', self::allowedMimes()),
            'extensions:'.implode(',', self::allowedExtensions()),
        ];
    }

    public static function maxSizeKb(): int
    {
        return config('security.uploads.max_size_kb');
    }

    /**
     * @return array<int, string>
     */
    public static function allowedMimes(): array
    {
        return config('security.uploads.allowed_mimes');
    }

    /**
     * @return array<int, string>
     */
    public static function allowedExtensions(): array
    {
        return config('security.uploads.allowed_extensions');
    }
}
