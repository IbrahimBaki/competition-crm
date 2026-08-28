<?php

namespace App\Domains\Security\Actions;

use App\Domains\Security\Exceptions\InvalidTwoFactorCodeException;
use App\Domains\Security\Services\AuditLogger;
use App\Models\User;
use PragmaRX\Google2FA\Google2FA;

class CompleteTwoFactorChallenge
{
    public function __construct(private AuditLogger $auditLogger) {}

    public function execute(User $user, string $code): void
    {
        if (! $user->hasTwoFactorEnabled()) {
            throw new InvalidTwoFactorCodeException('Two-factor authentication is not enabled.');
        }

        $google2fa = new Google2FA;
        $google2fa->setWindow(1);

        $codes = $user->two_factor_recovery_codes ?? [];

        if ($google2fa->verifyKey($user->two_factor_secret, $code)) {
            $this->auditLogger->record($user, 'two_factor.verified', $user, null, [
                'method' => 'totp',
            ]);

            return;
        }

        if (in_array($code, $codes, true)) {
            $codes = array_values(array_diff($codes, [$code]));
            $user->update(['two_factor_recovery_codes' => $codes]);

            $this->auditLogger->record($user, 'two_factor.verified', $user, null, [
                'method' => 'recovery_code',
                'remaining_codes' => count($codes),
            ]);

            return;
        }

        throw new InvalidTwoFactorCodeException('Invalid two-factor code.');
    }
}
