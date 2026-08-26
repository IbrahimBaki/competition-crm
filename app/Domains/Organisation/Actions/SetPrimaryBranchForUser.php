<?php

namespace App\Domains\Organisation\Actions;

use App\Domains\Organisation\Exceptions\UserBranchNotAttachedException;
use App\Domains\Security\Services\AuditLogger;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;

class SetPrimaryBranchForUser
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function execute(User $user, string $branchId, ?Authenticatable $actor = null): void
    {
        \DB::transaction(function () use ($user, $branchId, $actor) {
            $pivot = $user->branches()->where('branch_id', $branchId)->lockForUpdate()->first();
            if (! $pivot) {
                throw new UserBranchNotAttachedException;
            }

            $before = $user->branches()->get()->map(fn ($b) => ['branch_id' => $b->id, 'is_primary' => $b->pivot->is_primary])->toArray();

            // Unset all primary flags for this user
            $user->branches()->update(['is_primary' => false]);

            // Set the target branch as primary
            $user->branches()->updateExistingPivot($branchId, ['is_primary' => true]);

            $after = $user->branches()->get()->map(fn ($b) => ['branch_id' => $b->id, 'is_primary' => $b->pivot->is_primary])->toArray();

            $this->auditLogger->record(
                $actor,
                'organisation.user_branch.primary_set',
                $user,
                ['branches' => $before],
                ['branches' => $after, 'primary_branch_id' => $branchId]
            );
        });
    }
}
