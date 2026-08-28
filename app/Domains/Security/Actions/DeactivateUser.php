<?php

namespace App\Domains\Security\Actions;

use App\Domains\Security\Exceptions\CannotDeactivateLastAdministratorException;
use App\Domains\Security\Exceptions\CannotDeactivateSelfException;
use App\Domains\Security\Services\AuditLogger;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DeactivateUser
{
    public function __construct(private AuditLogger $auditLogger) {}

    public function execute(User $actor, User $target): void
    {
        if ($actor->uuid === $target->uuid) {
            throw new CannotDeactivateSelfException('Cannot deactivate yourself.');
        }

        $activeAdmins = DB::table('user_role')
            ->join('roles', 'user_role.role_id', '=', 'roles.id')
            ->join('users', 'user_role.user_uuid', '=', 'users.uuid')
            ->where('roles.name', 'administrator')
            ->whereNull('users.deactivated_at')
            ->distinct()
            ->count('users.uuid');

        if ($activeAdmins <= 1 && $target->roles()->where('name', 'administrator')->exists()) {
            throw new CannotDeactivateLastAdministratorException('Cannot deactivate the last active administrator.');
        }

        $target->update([
            'deactivated_at' => now(),
            'deactivated_by_uuid' => $actor->uuid,
        ]);

        $target->tokens()->delete();
        DB::table('sessions')->where('user_id', $target->getAuthIdentifier())->delete();

        $this->auditLogger->record($actor, 'user.deactivated', $target, null, ['user_uuid' => $target->uuid]);
    }
}
