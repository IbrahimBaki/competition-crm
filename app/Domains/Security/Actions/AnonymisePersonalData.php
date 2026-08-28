<?php

namespace App\Domains\Security\Actions;

use App\Domains\Security\Exceptions\CannotAnonymiseLastAdministratorException;
use App\Domains\Security\Exceptions\UserAlreadyAnonymisedException;
use App\Domains\Security\Services\AuditLogger;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AnonymisePersonalData
{
    public function __construct(private AuditLogger $auditLogger) {}

    public function execute(User $actor, User $target, string $reason): void
    {
        if ($target->anonymised_at !== null) {
            throw new UserAlreadyAnonymisedException('This user has already been anonymised.');
        }

        $activeAdmins = DB::table('user_role')
            ->join('roles', 'user_role.role_id', '=', 'roles.id')
            ->join('users', 'user_role.user_uuid', '=', 'users.uuid')
            ->where('roles.name', 'administrator')
            ->whereNull('users.anonymised_at')
            ->distinct()
            ->count('users.uuid');

        if ($activeAdmins <= 1 && $target->roles()->where('name', 'administrator')->exists()) {
            throw new CannotAnonymiseLastAdministratorException('Cannot anonymise the last active administrator.');
        }

        $anonymousName = "Erased user #{$target->getKey()}";
        $anonymousEmail = 'erased-'.Str::uuid().'@internal.invalid';

        $target->name = $anonymousName;
        $target->email = $anonymousEmail;
        $target->password = '';
        $target->two_factor_secret = null;
        $target->two_factor_recovery_codes = null;
        $target->two_factor_confirmed_at = null;
        $target->anonymised_at = now();
        $target->save();

        $target->tokens()->delete();
        DB::table('sessions')->where('user_id', $target->getAuthIdentifier())->delete();
        DB::table('user_invitations')->where('email', $target->email)->update(['status' => 'cancelled']);

        $this->auditLogger->record(
            $actor,
            'personal_data.erased',
            $target,
            null,
            [
                'subject_uuid' => $target->uuid,
                'actor_uuid' => $actor->uuid,
                'reason' => $reason,
                'fields_cleared' => ['name', 'email', 'password', 'two_factor_secret', 'two_factor_recovery_codes'],
            ]
        );
    }
}
