<?php

namespace App\Domains\Security\Actions;

use App\Domains\Security\Services\AuditLogger;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class CompletePasswordReset
{
    public function __construct(private AuditLogger $auditLogger) {}

    public function execute(string $email, string $token, string $password): void
    {
        $user = User::where('email', $email)->first();

        if (! $user || ! $user->isActive()) {
            throw new \InvalidArgumentException('Invalid reset token.');
        }

        $status = Password::reset(
            ['email' => $email, 'password' => $password, 'password_confirmation' => $password, 'token' => $token],
            function (User $u, string $pwd) {
                $u->forceFill([
                    'password' => Hash::make($pwd),
                    'failed_login_count' => 0,
                    'locked_until' => null,
                ])->save();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            $this->auditLogger->record(null, 'password.reset_completed', $user, null, ['email' => $email]);
        } else {
            throw new \InvalidArgumentException('Invalid or expired reset token.');
        }
    }
}
