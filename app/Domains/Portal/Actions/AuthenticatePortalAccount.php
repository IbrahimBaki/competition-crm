<?php

namespace App\Domains\Portal\Actions;

use App\Domains\Portal\Exceptions\PortalAccountNotVerifiedException;
use App\Domains\Portal\Exceptions\PortalSessionInvalidException;
use App\Domains\Portal\Models\PortalAccount;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;

class AuthenticatePortalAccount
{
    public function execute(string $email, string $password, string $ip): string
    {
        $key = 'portal-login:'.sha1($email).':'.$ip;

        if (RateLimiter::tooManyAttempts($key, 5)) {
            throw new PortalSessionInvalidException;
        }

        RateLimiter::hit($key, 15 * 60);

        $account = PortalAccount::where('email', $email)->first();

        if (! $account || $account->email_verified_at === null || $account->deactivated_at !== null) {
            throw new PortalAccountNotVerifiedException;
        }

        if ($account->locked_until && now()->isBefore($account->locked_until)) {
            throw new PortalSessionInvalidException;
        }

        if (! Hash::check($password, $account->password)) {
            $account->increment('failed_login_attempts');

            if ($account->failed_login_attempts >= 10) {
                $account->update(['locked_until' => now()->addMinutes(30)]);
            }

            throw new PortalAccountNotVerifiedException;
        }

        $account->update([
            'failed_login_attempts' => 0,
            'locked_until' => null,
            'last_login_at' => now(),
        ]);

        return $account->createToken('portal', ['portal'])->plainTextToken;
    }
}
