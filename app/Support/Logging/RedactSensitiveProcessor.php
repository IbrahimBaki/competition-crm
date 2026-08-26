<?php

namespace App\Support\Logging;

use Monolog\LogRecord;

final class RedactSensitiveProcessor
{
    private const SENSITIVE_KEYS = [
        'password',
        'password_confirmation',
        'token',
        'access_token',
        'refresh_token',
        'secret',
        'authorization',
        'cookie',
        'recovery_code',
        'otp',
        'two_factor_code',
        'invitation_token',
    ];

    private const PII_KEYS = ['email', 'phone', 'national_id', 'full_name'];

    public function __invoke(LogRecord $record): LogRecord
    {
        return $record->with(
            context: $this->redact($record->context),
            extra: $this->redact($record->extra),
        );
    }

    private function redact(mixed $data): mixed
    {
        if (! is_array($data)) {
            return $data;
        }

        $result = $data;

        foreach ($result as $key => &$value) {
            $lowerKey = strtolower($key);

            // Check for sensitive keys
            if (in_array($lowerKey, self::SENSITIVE_KEYS, true)) {
                $value = '[redacted]';
            } elseif (is_array($value)) {
                // Check for PII bundles at this level
                if ($this->isPiiBundle($value)) {
                    $value = '[redacted:pii]';
                } else {
                    // Recursively redact nested arrays
                    $value = $this->redact($value);
                }
            }
        }

        return $result;
    }

    private function isPiiBundle(mixed $data): bool
    {
        if (! is_array($data)) {
            return false;
        }

        $keys = array_map('strtolower', array_keys($data));

        // Check for email + phone, email + national_id, or email + full_name
        $hasEmail = in_array('email', $keys, true);
        $hasPhone = in_array('phone', $keys, true);
        $hasNationalId = in_array('national_id', $keys, true);
        $hasFullName = in_array('full_name', $keys, true);

        return $hasEmail && ($hasPhone || $hasNationalId || $hasFullName);
    }
}
