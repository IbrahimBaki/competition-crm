<?php

namespace App\Domains\Organisation\Actions;

use App\Domains\Security\Services\AuditLogger;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;

class AttachUserToBranch
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function execute(User $user, string $branchId, ?Authenticatable $actor = null): void
    {
        \DB::transaction(function () use ($user, $branchId, $actor) {
            if (! $user->branches()->where('branch_id', $branchId)->exists()) {
                $user->branches()->attach($branchId, ['created_at' => now(), 'updated_at' => now()]);
            }

            $this->auditLogger->record(
                $actor,
                'organisation.user_branch.attached',
                $user,
                null,
                ['branch_id' => $branchId]
            );
        });
    }
}
