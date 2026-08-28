<?php

namespace App\Domains\Security\Actions;

use App\Domains\Security\Exceptions\AccountDeactivatedException;
use App\Domains\Security\Exceptions\AccountLockedException;
use App\Domains\Security\Services\AuditLogger;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;

class AuthenticateStaff
{
    public function __construct(private AuditLogger $auditLogger) {}

    public function execute(string $email, string $password, string $ip): User
    {
        $key = 'login:'.sha1($email).':'.$ip;

        if (RateLimiter::tooManyAttempts($key, 10)) {
            throw new AccountLockedException('Account is locked due to too many failed login attempts. Try again later.');
        }

        RateLimiter::hit($key, 15 * 60);

        $user = User::where('email', $email)->first();

        if (! $user || ! $user->isActive()) {
            $this->auditLogger->record(null, 'login.failed', $user ?? new User(['email' => $email]), null, [
                'email' => $email,
                'reason' => $user ? 'deactivated' : 'not_found',
                'ip' => $ip,
            ]);
            throw new AccountDeactivatedException('Invalid credentials.');
        }

        if ($user->isLocked()) {
            $this->auditLogger->record(null, 'login.failed', $user, null, [
                'email' => $email,
                'reason' => 'locked',
                'ip' => $ip,
            ]);
            $remaining = $user->locked_until ? ($user->locked_until->getTimestamp() - time()) : 0;
            throw new AccountLockedException('Account is locked. Try again after '.$remaining.' seconds.');
        }

        if (! Hash::check($password, $user->password)) {
            $user->increment('failed_login_count');

            if ($user->failed_login_count >= 10) {
                $user->update(['locked_until' => now()->addMinutes(30)]);
            }

            $this->auditLogger->record(null, 'login.failed', $user, null, [
                'email' => $email,
                'reason' => 'invalid_password',
                'ip' => $ip,
            ]);

            throw new AccountDeactivatedException('Invalid credentials.');
        }

        $user->update([
            'failed_login_count' => 0,
            'locked_until' => null,
            'last_login_at' => now(),
        ]);

        $this->auditLogger->record($user, 'login.success', $user, null, ['ip' => $ip]);

        return $user;
    }
}
