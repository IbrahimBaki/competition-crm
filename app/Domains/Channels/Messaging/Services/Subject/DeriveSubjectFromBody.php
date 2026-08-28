<?php

namespace App\Domains\Channels\Messaging\Services\Subject;

final class DeriveSubjectFromBody
{
    public function derive(string $body, int $maxLength = 80, string $placeholder = ''): string
    {
        $trimmed = trim($body);

        if (empty($trimmed)) {
            return $placeholder;
        }

        if (mb_strlen($trimmed, 'UTF-8') <= $maxLength) {
            return $trimmed;
        }

        $truncated = mb_substr($trimmed, 0, $maxLength, 'UTF-8');

        $lastSpace = mb_strrpos($truncated, ' ', 0, 'UTF-8');

        if ($lastSpace === false || $lastSpace === 0) {
            return $truncated.'…';
        }

        return mb_substr($truncated, 0, $lastSpace, 'UTF-8').'…';
    }
}
