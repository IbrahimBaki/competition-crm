<?php

namespace App\Domains\Security\Actions;

use App\Domains\Security\Services\AuditLogger;
use App\Models\User;

class ActivateUser
{
    public function __construct(private AuditLogger $auditLogger) {}

    public function execute(User $actor, User $target): void
    {
        $target->update([
            'deactivated_at' => null,
            'deactivated_by_uuid' => null,
        ]);

        $this->auditLogger->record($actor, 'user.activated', $target, null, ['user_uuid' => $target->uuid]);
    }
}
