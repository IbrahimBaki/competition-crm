<?php

namespace App\Domains\Security\Actions;

use App\Domains\Security\Exceptions\TwoFactorRequiredException;
use App\Domains\Security\Permissions\PermissionKey;
use App\Domains\Security\Services\AuditLogger;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DisableTwoFactor
{
    public function __construct(private AuditLogger $auditLogger) {}

    public function execute(User $user, string $password): void
    {
        if (! Hash::check($password, $user->password)) {
            throw new \InvalidArgumentException('Invalid password.');
        }

        $requireTwoFactor = DB::table('auth_settings')->where('id', 1)->value('require_two_factor');
        if ($requireTwoFactor && ! $user->can(PermissionKey::ADMIN_USERS_MANAGE_TWO_FACTOR_POLICY)) {
            throw new TwoFactorRequiredException('Organization policy requires two-factor authentication.');
        }

        $user->update([
            'two_factor_secret' => null,
            'two_factor_confirmed_at' => null,
            'two_factor_recovery_codes' => null,
        ]);

        $this->auditLogger->record($user, 'two_factor.disabled', $user, null, [
            'user_uuid' => $user->uuid,
        ]);
    }
}
