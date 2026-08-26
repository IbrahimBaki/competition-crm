<?php

namespace App\Domains\Security\Actions;

use App\Domains\Security\Services\AuditLogger;
use App\Models\User;
use Illuminate\Support\Facades\Password;

class RequestPasswordReset
{
    public function __construct(private AuditLogger $auditLogger) {}

    public function execute(string $email): void
    {
        $user = User::where('email', $email)->first();

        if ($user) {
            $this->auditLogger->record(null, 'password.reset_requested', $user, null, ['email' => $email]);
        }

        Password::sendResetLink(['email' => $email]);
    }
}
