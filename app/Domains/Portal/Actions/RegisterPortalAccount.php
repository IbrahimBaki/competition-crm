<?php

namespace App\Domains\Portal\Actions;

use App\Domains\Portal\Models\PortalAccount;
use App\Domains\Portal\Models\PortalVerificationToken;
use Illuminate\Support\Str;

class RegisterPortalAccount
{
    public function handle(string $email, string $password, ?string $locale = null): PortalAccount
    {
        $account = PortalAccount::create([
            'uuid' => (string) Str::uuid(),
            'customer_id' => null,
            'email' => $email,
            'password' => bcrypt($password),
            'locale' => $locale,
            'email_verified_at' => null,
        ]);

        $plainToken = Str::random(64);
        $tokenHash = hash('sha256', $plainToken);

        PortalVerificationToken::create([
            'portal_account_id' => $account->id,
            'contact_channel' => 'email',
            'contact_value_hash' => hash('sha256', $email),
            'token_hash' => $tokenHash,
            'expires_at' => now()->addHours(24),
        ]);

        $account->verification_token = $plainToken;

        return $account;
    }
}
