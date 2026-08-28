<?php

namespace App\Domains\Security\Actions;

use PragmaRX\Google2FA\Google2FA;

class EnableTwoFactor
{
    public function execute(): array
    {
        $google2fa = new Google2FA;
        $secret = $google2fa->generateSecretKey();

        $qrCode = $google2fa->getQRCodeUrl(
            config('app.name'),
            'support-crm',
            $secret
        );

        $recoveryCodes = array_map(fn () => bin2hex(random_bytes(4)), range(1, 10));

        return [
            'secret' => $secret,
            'qr_code' => $qrCode,
            'recovery_codes' => $recoveryCodes,
        ];
    }
}
