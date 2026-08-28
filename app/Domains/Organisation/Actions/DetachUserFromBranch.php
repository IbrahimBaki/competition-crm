<?php

namespace App\Domains\Organisation\Actions;

use App\Domains\Organisation\Exceptions\UserBranchNotAttachedException;
use App\Domains\Security\Services\AuditLogger;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;

class DetachUserFromBranch
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function execute(User $user, string $branchId, ?Authenticatable $actor = null): void
    {
        \DB::transaction(function () use ($user, $branchId, $actor) {
            $pivot = $user->branches()->where('branch_id', $branchId)->first();
            if (! $pivot) {
                throw new UserBranchNotAttachedException;
            }

            if ($pivot->pivot->is_primary) {
                throw new \Exception('Cannot detach primary branch', 409);
            }

            $user->branches()->detach($branchId);

            $this->auditLogger->record(
                $actor,
                'organisation.user_branch.detached',
                $user,
                ['branch_id' => $branchId],
                null
            );
        });
    }
}
