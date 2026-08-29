<?php

namespace App\Domains\Security\Actions;

use App\Domains\Security\Exceptions\InvalidTwoFactorCodeException;
use App\Domains\Security\Services\AuditLogger;
use App\Models\User;
use PragmaRX\Google2FA\Google2FA;

class ConfirmTwoFactor
{
    public function __construct(private AuditLogger $auditLogger) {}

    public function execute(User $user, string $secret, string $code, array $recoveryCodes): void
    {
        $google2fa = new Google2FA;
        $google2fa->setWindow(1);

        if (! $google2fa->verifyKey($secret, $code)) {
            throw new InvalidTwoFactorCodeException('Invalid two-factor code.');
        }

        $user->forceFill([
            'two_factor_secret' => $secret,
            'two_factor_recovery_codes' => $recoveryCodes,
            'two_factor_confirmed_at' => now(),
        ])->save();

        $this->auditLogger->record($user, 'two_factor.enrolled', $user, null, [
            'recovery_codes_count' => count($recoveryCodes),
        ]);
    }
}
